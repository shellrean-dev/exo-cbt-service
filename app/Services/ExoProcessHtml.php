<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use function Complex\theta;

class ExoProcessHtml
{
    private $new_name_path;
    private $target_dir;
    private $content;
    private $dsn;
    private $directory;
    private $original_name;
    private $after_extract;
    private $files;

    private $correct_position = [
        'A' => 0,
        'B' => 1,
        'C' => 2,
        'D' => 3,
        'E' => 4,
        'F' => 5,
        'G' => 6,
        'H' => 7
    ];

    public function __construct($filepath, $directory, $original_name)
    {
        $this->directory = $directory;
        $this->target_dir = storage_path('app/public/'.$directory->slug.'/');

        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
            || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $hos = request()->server('HTTP_HOST');
        $this->dsn = $protocol.$hos;

        $target_file = $filepath;
        $info = pathinfo($target_file);
        $new_name = $info['filename']. '.Zip';
        $this->original_name = pathinfo($original_name, PATHINFO_FILENAME);
        $this->new_name_path = storage_path('app/public/'.$directory->slug.'/'.$new_name);

        rename($target_file, $this->new_name_path);
    }

    public function render()
    {
        try {
            $this->_extract_zip();
            $this->_get_xml_content();

            $doc = new \DOMDocument();
            $doc->loadHTML('<?xml encoding="utf-8" ?>' .$this->content);

            $image_urls = [];
            $images = $doc->getElementsByTagName('img');
            foreach($images as $image) {
                if($image instanceof \DOMElement) {
                    $image_urls[] = $image->getAttribute('src');
                }
            }
            $images = [];
            $word_folder = $this->target_dir.$this->original_name;
            $iterate = 0;
            foreach ($image_urls as $key => $value) {
                $ext_img = strtolower(pathinfo($value, PATHINFO_EXTENSION));
                $imagenew_name= time().$iterate.".".$ext_img;
                $old_path=$word_folder."/".$value;
                $new_path=$this->target_dir.$imagenew_name;

                if(file_exists($old_path)) {
                    $image = Image::make($old_path)->encode('webp', 90);
                    $new_path_storage = "public/".$this->directory->slug.'/'.$imagenew_name.'.webp';
                    Storage::put($new_path_storage, $image->__toString());

                    array_push($images, [
                        'id'            => Str::uuid()->toString(),
                        'directory_id'	=> $this->directory->id,
                        'filename'		=> $imagenew_name.'.webp',
                        'path'			=> $new_path_storage,
                        'exstension'	=> $ext_img,
                        'dirname'		=> $this->directory->slug,
                        'size'			=> 0,
                        'created_at'    => now(),
                        'updated_at'    => now()
                    ]);

                    rename($old_path,$new_path);
                    $img = $this->dsn.'/storage/'.$this->directory->slug.'/'.$imagenew_name.'.webp';
                    $this->content = str_replace($value,$img,$this->content);
                }
                $iterate++;
            }
            $this->files = $images;

            $data = [];
            $doc->loadHTML('<?xml encoding="utf-8" ?>' .$this->content);
            $body = $doc->getElementsByTagName('body');
            if ( $body && 0 < $body->length ) {
                $body = $body->item(0);
                foreach($body->childNodes as $table) {
                    if ($table->nodeName == "table") {
                        $element = [
                            'pertanyaan' => '',
                            'correct'    => [],
                            'options'    => [],
                            'type'       => 0,
                        ];

                        $real_iterate = 0;
                        foreach($table->childNodes as $iterate => $tr) {
                            if($tr instanceof \DOMElement) {
                                $td = $tr->childNodes;
                                $key = $td->item(1);
                                $value = $td->item(3);

                                if ($real_iterate == 0) {
                                    $element['pertanyaan'] = $this->_dom_inner_html($value);
                                    $real_iterate += 1;
                                    continue;
                                }
                                if (trim(strip_tags($key->nodeValue)) == ":::") {
                                    $correct_op = array_filter(explode(',',$value->nodeValue));
                                    $correct_option_position = array();
                                    foreach($correct_op as $v){
                                        $kj = trim(strip_tags($v));
                                        if(isset($this->correct_position[$kj])) {
                                            $correct_option_position[] = $this->correct_position[$kj];
                                        } else {
                                            File::delete($this->new_name_path);
                                            File::deleteDirectory($this->target_dir.$this->original_name);
                                            throw new \Exception('Tidak ada jawaban untuk huruf: '.$kj);
                                        }
                                    }
                                    $element['correct'] = $correct_option_position;
                                    $real_iterate += 1;
                                    continue;
                                }

                                array_push($element['options'], $this->_dom_inner_html($value));
                            }
                        }
                        if (count($element['correct']) > 1) {
                            $element['type'] = 4;
                        } else if (count($element['correct']) == 1) {
                            $element['type'] = 1;
                        } else if (count($element['correct']) == 0) {
                            if (count($element['options']) > 0) {
                                $element['type'] = 6;
                            } else {
                                $element['type'] = 2;
                            }
                        }
                        array_push($data, $element);
                    }
                }
            }
            File::delete($this->new_name_path);
            File::deleteDirectory($this->target_dir.$this->original_name);

            return [
                'data' => $data,
                'files' => $this->files,
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function _extract_zip()
    {
        $zip = new \ZipArchive();
        if ($zip->open($this->new_name_path) == true ) {
            $zip->extractTo($this->target_dir);
            $zip->close();
        }
    }

    private function _get_xml_content()
    {
        $word_xml = $this->target_dir.$this->original_name.'/'.$this->original_name.'.html';
        $this->content = file_get_contents($word_xml);
        $this->content = iconv('UTF-8', 'UTF-8//IGNORE', $this->content);
        $this->_strip_tags();
    }

    private function _strip_tags()
    {
        $this->content = strip_tags($this->content, "<u><p><b><i><ul><ol><li><img><span><table><tr><td>");
        $this->content = preg_replace("/text-indent[^>]*;/is", "", $this->content);
        $this->content = trim(preg_replace('/\s\s+/', ' ', $this->content));
    }

    private function _dom_inner_html(\DOMNode $element)
    {
        $innerHTML = "";
        $children  = $element->childNodes;

        foreach ($children as $child)
        {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

        return $innerHTML;
    }
}
