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
        if (!$req->hasFile("xml")) {
            return $this->error("No file");
        }

        if (strpos($req->file("xml")->getClientOriginalName(), ".xml") === false) {
            return $this->error("It should be xml file");
        }

        $filename = uniqid() . ".xml";
        //not a good for streams
//        $result = $req->file("xml")->storeAs("files", $filename);
        $result = Storage::disk('local')->put("files/" . $filename, $req->file("xml")->get());

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
