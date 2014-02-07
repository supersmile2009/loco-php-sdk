#!/usr/bin/env php
<?php
/**
 * Convert Swagger docs to Guzzle service definition.
 */

require_once __DIR__.'/vendor/autoload.php';

use Loco\Swagger\DocsClient;
use Loco\Swagger\DocsModel;


// pull root resource listing from remote docs
$client = DocsClient::factory();
$resources = $client->getResources();
$version = $resources->getApiVersion();

printf("Loco API version %s\n", $version );

// instantiate Swagger->Guzzle transformer
$service = new DocsModel( 'loco', 'Loco REST API client', $version );

// register custom response classes - meaningless in Swagger terms
// $service->registerResponseClass('\Loco\Http\Response\RawResponse');


// pull each api declaration and add to service dedcription
foreach( $resources->getApiPaths() as $path ){
    printf(" pulling %s ...\n", $path );
    usleep( 250000 );
    $declaration = $client->getDeclaration( compact('path') );
    foreach( $declaration->getApis() as $api ){
        printf(" + adding %s ...\n", $api['path'] );
        $service->addSwaggerApi( $api );
    }
}

// inspect service definition
// echo $service->toJson();

// output PHP array for including from Loco\Http\ApiClient
$source = "<?php\nreturn ".$service->export().";\n";
$target = __DIR__.'/src/Loco/Http/Resources/service.php';  
file_put_contents( $target, $source );
echo "Service description saved to ",$target,"\n";