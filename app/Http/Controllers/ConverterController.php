<?php
/**
 * Created by PhpStorm.
 * User: tegaadigu
 * Date: 06/04/2017
 * Time: 4:47 PM
 */

namespace App\Http\Controllers;


use Converter\Storage\Azure\Blob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ConverterController
{
    /**
     * @param Request $request
     * @return JsonResponse|string
     */
    public function convert(Request $request): JsonResponse
    {
        $url = $request->get('url');
        $mblob = new Blob('/pdf-converter');
        $arrContextOptions = [
            "ssl" => [
                "verify_peer"      => false,
                "verify_peer_name" => false,
            ],
        ];
        $view = file_get_contents($url, false, stream_context_create($arrContextOptions));
        $snappy = App::make('snappy.pdf');

        $filename = uniqid().'.pdf';
        $file = fopen($filename, 'w');
        fwrite($file, $snappy->getOutputFromHtml($view));
        fclose($file);
        $url = $mblob->uploadBlob($filename, $filename);

        return response()->json(['url' => $url, 'status' => 200, 'name' => $filename]);
    }
}