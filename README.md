Proof of concept per Eloquent models con attributi serializzati.
La logica e' tutta in `App\Meta\MetaModel`, il resto e' codice di esempio/test.

Istruzioni:

- il model deve estendere `App\Meta\MetaModel` invece di `Model`
- la tabella deve avere una colonna 'metadata' di tipo json, aggiungendo quanto segue alla migration:
  `$table->json('metadata')->nullable();`
- Il model deve avere un array `$metaDeny` con tutti gli attributi che non devono essere serializzati, es:
    `protected array $metaDeny = ['id', 'created_at', 'updated_at', ...];`
  In alternativa, e' possibile specificare i fields che devono essere serializzati in un array `$metaAllow`.
  Questo permette che vengano salvati, ad es. per errori di spelling, metadati sbagliati.

In generale e' consigliabile `$metaAllow` anziche' `$metaDeny` a meno che i campi serializzati devono essere
completamente liberi e non seguire alcuno schema.

Ci sono due modelli demo, `Product` e `Book`. In un caso di usa `metaAllow`, nell'altro `metaDeny`.
Vedere il codice di esempio in `MetaModelTest`.

Esempio di uso:

```php
    $product = new \App\Models\Product();
    $product->name = 'Test';
    $product->description = 'A test product';
    $product->price = '9.99';
    $product->this_is_a_custom_meta = 'Custom meta value';      // questo e' serializzato
    $product->this_is_another_meta = 'Another meta value';      // questo e' serializzato
    $product->save();
```

Qualsiasi attributo serializzato puo' essere letto e scritto come un normale attributo.

# Test Coverage
Come esempio
In caso di modifiche, lanciare i test automatici (`artisan test`).
I test vengono eseguiti su un sqlite in memory, by default. Se si vuole lanciare i test su
postgresql:
- crea `.env.testing` che punta a DB Postgres
- in `phpunit.xml` commentare `DB_CONNECTION` e `DB_DATABASE`

LIMITAZIONI:
- non funziona mass update. I campi serializzati possono essere solo gestiti recuperando l'intero
  model dal DB, aggiornandolo e risalvandolo.
- se si serializza il model, i campi serializzati appaiono sotto l'attributo `metadata`.
  Da codice, invece, possono essere utilizzati direttamente.


