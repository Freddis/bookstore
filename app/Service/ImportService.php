<?php


namespace App\Service;


use App\Models\Book;
use App\Models\CatalogueParseJob;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Throwable;

class ImportService
{

    public function process($filename)
    {
        $handle = Storage::disk('local')->readStream("files/" . $filename);
        if (!$handle) {
            throw new \Exception("Can't access the file");
        }

        Book::getQuery()->delete();
        $i = 0;
        $last = 0;
        $threshold = 100;
        while ($book = $this->getElement($handle, "book")) {
//           dump($book);
            $this->processBook($book);
            gc_collect_cycles();
            $i++;
            if ($i - $last > $threshold) {
                $last = $i;
//                dump($i);
            }
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

    private function getElement($file, $elementName)
    {
        $start = "<" . $elementName;
        $end = "</" . $elementName;
        $this->readUntil($file, $start);
        $element = $this->readUntil($file, $end);
        if ($element) {
            $element = $start . $element . ">";
        }
        return $element;
    }

    private function readUntil($handle, $end)
    {
        $str = "";
        while ("" !== ($char = fread($handle, 1))) {
            $str .= $char;

            $expectedPos = strlen($str) - strlen($end);
            $offset = $expectedPos > 0 ? $expectedPos : 0;
            $pos = strpos($str, $end, $offset);
            if ($pos === $expectedPos) {
                $next = $this->peekNextChar($handle);
                if ($next == " " || $next == ">") {
                    return $str;
                }
            }
        }
        return null;
    }

    private function peekNextChar($handle)
    {
        $current = ftell($handle);
        $char = fread($handle, 1);
        fseek($handle, $current);
        return $char;
    }

    private function processBook(string $xmlString)
    {
        $xml = simplexml_load_string($xmlString);
        $attrs = $xml->attributes();
        $id = $attrs["isbn"] . "";
        $title = $attrs["title"] . "";
        $description = isset($xml->description) ? $xml->description . "" : "";
        $image = isset($xml->image) ? $xml->image . "" : null;

        $existing = Book::where("isbn", $id)->first();
        if ($existing) {
            return;
        }


        $book = new Book();
        $book->title = $title;
        $book->description = $description;
        $book->isbn = $id;
        if ($image) {
            $localImage = $this->loadImage($image);
            $book->image = $localImage;
        }
        $book->save();
//        dd($result);

    }

    private function loadImage(string $image)
    {
        $path = "storage/" . date("Y") . "/" . date("m") . "/" . date("d");
        $dirpath = public_path() . "/" . $path;
        File::ensureDirectoryExists($dirpath);
        $content = file_get_contents($image);
        $newName = uniqid() . ".jpg";
        $filePath = $dirpath . "/" . $newName;
        $image = Image::make($content);

        $desiredWidth = 400;
        $desiredHeight = 200;

        if( $image->width() < $desiredWidth)
        {
            $image = $image->widen($desiredWidth);
        }
        if( $image->height() < $desiredHeight)
        {
            $image = $image->widen($desiredHeight);
        }

        $initialWidth = $image->width();
        $initialHeight = $image->height();


        if($initialWidth/$initialHeight < $desiredWidth/$desiredHeight)
        {
            $image = $image->resize($desiredWidth, $initialHeight, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        else {
            $image = $image->resize($initialWidth, $desiredHeight, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        $image = $image->crop($desiredWidth, $desiredHeight);
        $image->save($filePath);

        $uri = $path . "/" . $newName;
        return $uri;
    }
}