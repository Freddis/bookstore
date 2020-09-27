<?php


namespace App\Service;


use App\Models\CatalogueParseJob;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Jobs\Job;
use Throwable;

class ImportService
{

    public function process($filename)
    {
        $coin = rand(0,1) == 1 ? true : false;
        if(!$coin)
        {
            throw new \Exception(("Stuck"));
        }
    }


    public function jobCompleted(Job $data, Throwable $error = null)
    {
        $report = new CatalogueParseJob();
        $report->jobId = $data->getJobId();
        $report->status = $error == null ? true : false;
        $report->message = $error == null ? null : $error->getMessage();
        $report->save();
    }
}