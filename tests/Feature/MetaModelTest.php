<?php

use App\Models\Book;
use App\Models\Product;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNull;

use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

// Book uses $metaDeny
test('save book attributes', function() {
    $book = new Book();
    $book->title = 'Moby Dick';
    $book->description = 'A book about a white killer whale';
    $book->save();

    $record = DB::table('books')->find($book->id);
    assertEquals($record->title, $book->title);
    assertEquals($record->description, $book->description);
    assertNull($record->metadata);

    assert($book->isClean());
    $book->attribute_1 = 'attribute 1';
    assert($book->isDirty());
    $book->attribute_2 = 13;
    $book->save();

    // Facciamo delle verifiche a livello di record sul db (low level)
    $record = DB::table('books')->find($book->id);
    assertEquals($record->title, $book->title);
    $meta = json_decode($record->metadata);
    assertEquals($meta->attribute_1, 'attribute 1');
    assertEquals($meta->attribute_2, 13);

    // Verifichiamo se gli attributi serializzati a livello di model si comportano esattamente come gli altri
    $book2 = Book::find($book->id);
    assertEquals($book2->title, 'Moby Dick');
    assertEquals($book2->description, 'A book about a white killer whale');
    assertEquals($book2->attribute_1, 'attribute 1');
    assertEquals($book2->attribute_2, 13);
});

// Product uses $metaAllow
test('save product attributes', function() {
    $product = new Product();
    $product->name = 'IPhone';
    $product->description = 'A smartphone';
    $product->save();

    // Facciamo delle verifiche a livello di record sul db (low level)
    $record = DB::table('products')->find($product->id);
    assertEquals($record->name, $product->name);
    assertEquals($record->description, $product->description);
    assertNull($record->metadata);

    $product->this_is_a_custom_meta = 'attribute 1';
    $product->this_is_another_meta = 13;
    $product->save();

    // Facciamo delle verifiche a livello di record sul db (low level)
    $record = DB::table('products')->find($product->id);
    assertEquals($record->name, $product->name);
    $meta = json_decode($record->metadata);
    assertEquals($meta->this_is_a_custom_meta, 'attribute 1');
    assertEquals($meta->this_is_another_meta, 13);

    expect(function() use($product) {
        // Questo lo verifichiamo solo in un MetaModel con allowMeta, perche' in questo caso l'attributo deve stare nell'elenco, altrimenti errore
        $product->an_non_declared_meta = 'attribute 1';
        $product->save();
    })->toThrow(Exception::class);

    // Verifichiamo se gli attributi serializzati a livello di model si comportano esattamente come gli altri
    $product2 = Product::find($product->id);
    assertEquals($product2->name, 'IPhone');
    assertEquals($product2->description, 'A smartphone');
    assertEquals($product2->this_is_a_custom_meta, 'attribute 1');
    assertEquals($product2->this_is_another_meta, 13);
});
