<?php

namespace App\Http\Controllers;

use App\Jobs\ParseCatalogueXmlJob;
use App\Models\CatalogueParseJob;
use Illuminate\Http\Request;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Storage;

class CatalogueController extends Controller
{
    function upload(Request $req)
    {
        // read contents from the input stream
        $inputHandler = fopen('php://input', "r");

        $filename = uniqid() . ".xml";
        $result = Storage::disk('local')->writeStream("files/" . $filename, $inputHandler);

        if (!$result) {
            return $this->error("Couldn't move the file");
        }

        $handler = new ParseCatalogueXmlJob($filename);
        $job = $handler->onQueue(null);
        $id = $this->dispatch($job);
        return ["status" => true, "data" => ["jobId" => $id]];
    }

    function checkUploadStatus(Request $req)
    {
        $id = $req->get("id",null);
        if(!$id)
        {
            return $this->error("No id has been passed");
        }

        $job = CatalogueParseJob::where("jobId",$id)->first();
        if(!$job)
        {
            return ["status"=>true,"data" => ["jobStatus" => "pending"]];
        }

        if($job->status)
        {
            return ["status"=>true,"data" => ["jobStatus" => "success"]];
        }

        return ["status"=>true,"data" => ["jobStatus" => "failure","message" => $job->message]];
    }


    function error($error)
    {
        return ["status" => false, "errors" => [$error]];
    }
}
