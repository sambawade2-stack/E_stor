<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\Images\ImageService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    /**
     * Une photo est réencodée en WebP et redimensionnée : c'est ce qui
     * empêche un cliché de téléphone de plusieurs mégaoctets d'occuper
     * autant de place sur le serveur.
     */
    public function test_an_uploaded_photo_is_resized_and_converted_to_webp(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 2400, 1800);

        $path = app(ImageService::class)->store($file, 'products');

        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);

        $dimensions = getimagesizefromstring(Storage::disk('public')->get($path));

        $this->assertSame(1200, $dimensions[0], 'La largeur doit être ramenée à 1200 px.');
        $this->assertSame(900, $dimensions[1], 'Le rapport d\'aspect doit être conservé.');
        $this->assertSame('image/webp', $dimensions['mime']);
    }

    public function test_a_small_photo_is_never_upscaled(): void
    {
        $file = UploadedFile::fake()->image('petite.jpg', 400, 300);

        $path = app(ImageService::class)->store($file, 'products');

        $dimensions = getimagesizefromstring(Storage::disk('public')->get($path));

        $this->assertSame(400, $dimensions[0]);
        $this->assertSame(300, $dimensions[1]);
    }

    /**
     * Le poids d'un fichier ne dit rien du coût de son décodage : une image
     * uniforme de 12 000 × 12 000 ne pèse que 0,43 Mo mais réclame plus
     * d'un gigaoctet en mémoire. GD allouant hors du memory_limit de PHP,
     * aucune exception n'est levée : le conteneur est tué et le site tombe
     * pour tous les visiteurs. On mesure donc les dimensions avant de
     * décoder quoi que ce soit.
     */
    public function test_an_image_with_absurd_dimensions_is_refused_before_decoding(): void
    {
        config(['shop.max_image_pixels' => 10_000]); // 10 000 px² pour le test

        $file = UploadedFile::fake()->image('bombe.png', 200, 200); // 40 000 px²

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Image trop grande');

        app(ImageService::class)->store($file, 'products');
    }

    public function test_the_admin_form_rejects_an_oversized_image_with_a_clear_message(): void
    {
        config(['shop.max_image_pixels' => 10_000]);

        $category = Category::create(['name' => 'Chargeurs']);

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Chargeur 25W',
                'sku' => 'TEST-25W',
                'price' => 9500,
                'stock_quantity' => 5,
                'images' => [UploadedFile::fake()->image('bombe.png', 200, 200)],
            ])
            ->assertSessionHasErrors('images.0');

        $this->assertSame(0, Product::count(), 'Aucun produit ne doit être créé.');
    }

    /**
     * Le SVG passait la validation puis faisait échouer GD en erreur 500.
     * Conservé tel quel, il serait de surcroît un vecteur de XSS stocké.
     */
    public function test_a_brand_logo_cannot_be_an_svg(): void
    {
        $svg = UploadedFile::fake()->createWithContent(
            'logo.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
        );

        $this->actingAs($this->admin)
            ->post(route('admin.brands.store'), ['name' => 'Oraimo', 'logo' => $svg])
            ->assertSessionHasErrors('logo');
    }
}
