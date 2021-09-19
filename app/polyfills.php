<?php

if (!class_exists('MongoClient')) {
    // Backwards-compatibility with old mongo driver.
    class MongoClient extends \MongoDB\Client {}
}
