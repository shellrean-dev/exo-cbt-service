<?php
namespace App\Services;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class WordService
{
	public function wordFileImport($filepath, $directory)
	{
		$question_split = "/S:[0-9]+\)/";
		$option_split = "/[A-Z]:\)/";
		$correct_split = "/JAWAB:/";

		$target_dir = public_path(sprintf('storage/%s/', $directory->slug));
		$info = pathinfo($filepath);
		$new_name = $info['filename']. '.Zip';
		$new_name_path = public_path(sprintf('storage/exec171200/%s/%s', $directory->slug, $new_name));
		rename($filepath, $new_name_path);
		$zip = new \ZipArchive;

		if ($zip->open($new_name_path) == true ) {
			$zip->extractTo($target_dir);
			$zip->close();

			$word_xml = $target_dir."word/document.xml";
			$word_xml_relation = $target_dir."word/_rels/document.xml.rels";
			$content = file_get_contents($word_xml);
			$content = strip_tags($content, "<a:blip><w:p>");
			$content = preg_replace("/<w:p[^>]*>/is", "<p>", $content);
			$content = preg_replace("/<\/w:p>/is", "</p>", $content);
			$xml = simplexml_load_file($word_xml_relation);

			$supported_image = array(
				'gif',
				'jpg',
				'jpeg',
				'png'
			);

			$relation_image = array();
			foreach ($xml as $key => $qjd) {
				$ext = strtolower(pathinfo($qjd['Target'], PATHINFO_EXTENSION));
				if(in_array($ext, $supported_image)) {
					$id = $this->xml_attribute($qjd, 'Id');
					$target = $this->xml_attribute($qjd, 'Target');

					$relation_image[$id] = $target;
				}
			}
			$word_folder = $target_dir."word";
			$prop_folder = $target_dir."docProps";
			$relat_folder = $target_dir."_rels";
			$content_folder = $target_dir."[Content_Types].xml";

			$rand_inc_number = 1;
			foreach ($relation_image as $key => $value) {
				// $rplc_str='&lt;a:blip r:embed=&quot;'.$key.'&quot; cstate=&quot;print&quot;/&gt;';
				// $rplc_str1='&lt;a:blip r:embed=&quot;'.$key.'&quot; cstate=&quot;print&quot;&gt;&lt;/a:blip&gt;';
				// $rplc_str2='&lt;a:blip r:embed=&quot;'.$key.'&quot;&gt;&lt;/a:blip&gt;';
				// $rplc_str3='&lt;a:blip r:embed=&quot;'.$key.'&quot;/&gt;';
				$rplc_str = '<a:blip r:embed="'.$key.'" cstate="print"/>';
				$rplc_str1 = '<a:blip r:embed="'.$key.'" cstate="print"></a:blip>';
				$rplc_str2 = '<a:blip r:embed="'.$key.'"></a:blip>';
				$rplc_str3 = '<a:blip r:embed="'.$key.'"/>';

        		$ext_img = strtolower(pathinfo($value, PATHINFO_EXTENSION));
        		$imagenew_name=time().$rand_inc_number.".".$ext_img;
        		$old_path=$word_folder."/".$value;

				$image = Image::make($old_path)->encode('webp', 90);
				$new_path=$target_dir.$imagenew_name;

				$new_path_storage = $directory->slug.'/'.$imagenew_name.'.webp';
				Storage::put($new_path_storage, $image->__toString());

				DB::table('files')->insert([
					'id'			=> Str::uuid()->toString(),
					'directory_id'	=> $directory->id,
					'filename'		=> $imagenew_name.'.webp',
					'path'			=> $new_path_storage,
					'exstension'	=> $ext_img,
					'dirname'		=> $directory->slug,
					'size'			=> 0
				]);

				$img = sprintf('<img src="%s" />', sprintf('/storage/%s', $new_path_storage));
		        $content=str_replace($rplc_str,$img,$content);
		        $content=str_replace($rplc_str1,$img,$content);
		        $content=str_replace($rplc_str2,$img,$content);
		        $content=str_replace($rplc_str3,$img,$content);
		        $rand_inc_number++;
			}

			$this->rrmdir($word_folder);
			$this->rrmdir($relat_folder);
		    $this->rrmdir($prop_folder);
		    $this->rrmdir($content_folder);
		    $this->rrmdir($new_name_path);
            $this->rrmdir($target_dir."customXml");

		    $question_data=array();
		    $option=array();
		    $single_question="";
		    $singlequestion_array=array();
		    $expl=array_filter(preg_split($question_split,$content));

		    foreach($expl as $ekey =>  $value){
		    	$quesions[]=array_filter(preg_split($option_split,$value));
		    	foreach($quesions as $key => $options){
		    		$option_count=count($options);
		    		$question="";
		    		$option=array();
		    		foreach($options as $key_option => $val_option){
		    			if($option_count > 1){
		    				if($key_option == 0){
		    					$question=$val_option;
		    				} else {
		    					if($key_option == ($option_count-1)){
		    						if (preg_match($correct_split, $val_option, $match)) {
		    							$correct=array_filter(preg_split($correct_split,$val_option));
		    							$option[]=$correct['0'];
		    							$singlequestion_array[$key]['correct']=$correct['1'];
		    						} else {
		    							$option[]=$val_option;
		    							$singlequestion_array[$key]['correct']="";
		    						}
		    					} else {
		    						$option[]=$val_option;
		    					}
		    				}
		    			} else if ($option_count == "1") {
		    				if (preg_match($correct_split, $val_option, $match)) {
		    					$correct=array_filter(preg_split($correct_split,$val_option));
		    					$question=$correct['0'];
		    					$singlequestion_array[$key]['correct']=$correct['1'];
		    				} else {
		    					$question=$val_option;
                				$singlequestion_array[$key]['correct']="";
		    				}
		    			}
		    		}
		    		$question=array_filter(preg_split($question_split,$question));
          			$singlequestion_array[$key]['question']=$question[0];
          			$singlequestion_array[$key]['option']=$option;
		    	}
		    }


		    return $singlequestion_array;
		} else {
			return false;
		}
	}

	function xml_attribute($object, $attribute)
	{
	 	if(isset($object[$attribute]))
	 	return (string) $object[$attribute];
	}

	public function rrmdir($dir)
	{
	  if (is_dir($dir)) {
	    $objects = scandir($dir);
	    foreach ($objects as $object) {
	      if ($object != "." && $object != "..") {
	        if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
	      }
	    }
	    reset($objects);
	    if($dir!="uploads"){
	      rmdir($dir);
	    }
	  }else{
          if (file_exists($dir)) {
              unlink($dir);
          }
	  }
	}

}
