<?php

namespace Tests\Feature\Security;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ContentSecurityPolicyTest extends TestCase
{
    /**
     * La CSP de production autorise 'unsafe-eval' (requis par Alpine) mais
     * pas 'unsafe-inline'. Tout gestionnaire d'événement en ligne cesse donc
     * de fonctionner une fois déployé — sans la moindre erreur visible : le
     * menu déroulant change et rien ne se passe.
     *
     * Ce test balaie les vues pour empêcher d'en réintroduire. Utilisez
     * Alpine à la place : x-data @change="$el.form.submit()".
     */
    public function test_no_view_uses_an_inline_event_handler(): void
    {
        $offenders = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            // On ignore les commentaires Blade, qui citent parfois un
            // onsubmit= à titre d'explication historique.
            $contents = preg_replace('/\{\{--.*?--\}\}/s', '', $file->getContents());

            if (preg_match_all('/\son[a-z]+\s*=\s*"/i', (string) $contents, $matches)) {
                $offenders[] = str_replace(resource_path('views').'/', '', $file->getPathname())
                    .' ('.implode(', ', array_map('trim', $matches[0])).')';
            }
        }

        $this->assertSame([], $offenders, implode("\n", [
            'Gestionnaire(s) d\'événement en ligne détecté(s) — ils seront bloqués par la CSP en production :',
            ...$offenders,
        ]));
    }

    /**
     * Les directives sur lesquelles repose le reste du site : sans
     * 'unsafe-eval', Alpine ne peut plus évaluer x-data / x-on.
     */
    public function test_the_production_policy_keeps_the_directives_the_site_depends_on(): void
    {
        $this->app['env'] = 'production';

        $csp = $this->get(route('shop.home'))->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("script-src 'self' 'unsafe-eval'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $csp);
    }
}
