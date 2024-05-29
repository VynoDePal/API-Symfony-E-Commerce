<?php

namespace App\tests;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ProductApiControllerTest extends WebTestCase
{
    public function testGetProducts(): void
    {
        $client = static::createClient();
        $repository = static::getContainer()->get(ProductRepository::class);

        $product1 = (new Product())->setName('Product 1')->setPrice(10);
        $product2 = (new Product())->setName('Product 2')->setPrice(20);
        $repository->persist($product1, $product2);
        $repository->flush();

        $client->request('GET', '/api/products');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            [
                'name' => 'Product 1',
                'price' => 10
            ],
            [
                'name' => 'Product 2',
                'price' => 20
            ]
        ]);
    }

    public function testGetProduct(): void
    {
        $client = static::createClient();
        $repository = static::getContainer()->get(ProductRepository::class);

        $product = (new Product())->setName('Product 1')->setPrice(10);
        $repository->persist($product);
        $repository->flush();

        $client->request('GET', '/api/products/' . $product->getId());

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'name' => 'Product 1',
            'price' => 10
        ]);
    }

    public function testAddProduct(): void
    {
        $client = static::createClient();

        $request = new Request([], [
            'name' => 'Product 1',
            'price' => 10
        ]);
        $client->request($request->getMethod(), '/api/products', $request->request->all(), [], [], $request->server->all());

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'name' => 'Product 1',
            'price' => 10
        ]);
    }

    public function testModifyProduct(): void
    {
        $client = static::createClient();
        $repository = static::getContainer()->get(ProductRepository::class);

        $product = (new Product())->setName('Product 1')->setPrice(10);
        $repository->persist($product);
        $repository->flush();

        $request = new Request([], [
            'name' => 'Product 2',
            'price' => 20
        ]);
        $client->request($request->getMethod(), '/api/products/' . $product->getId(), $request->request->all(), [], [], $request->server->all());

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'name' => 'Product 2',
            'price' => 20
        ]);
    }

    public function testDeleteProduct(): void
    {
        $client = static::createClient();
        $repository = static::getContainer()->get(ProductRepository::class);

        $product = (new Product())->setName('Product 1')->setPrice(10);
        $repository->persist($product);
        $repository->flush();

        $client->request('DELETE', '/api/products/' . $product->getId());

        $this->assertResponseIsSuccessful();
    }
}
