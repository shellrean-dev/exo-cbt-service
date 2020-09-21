<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\JawabanSoal;
use App\Soal;

class SoalService
{
	public function importQues($question, $banksoal_id) 
	{
		foreach($question as $key => $singlequestion){
			if($key != 0){
				// $question= str_replace('"','&#34;',$singlequestion['question']);
				$question = $singlequestion['question'];
				$question= str_replace("`",'&#39;',$question);
		        $question= str_replace("‘",'&#39;',$question);
		        $question= str_replace("’",'&#39;',$question);
		        $question= str_replace("â€œ",'&#34;',$question);
		        $question= str_replace("â€˜",'&#39;',$question);

		        $question= str_replace("â€™",'&#39;',$question);
		        $question= str_replace("â€",'&#34;',$question);
		        $question= str_replace("'","&#39;",$question);
		        $question= str_replace("\n","<br>",$question);

		        $option_count=count($singlequestion['option']);
		        $ques_type="0";
		        if($option_count!="0"){
		         	if($singlequestion['correct']!=""){
		            	if (strpos($singlequestion['correct'],',') !== false) {
		              		$ques_type="1";
		            	}else{
		              		$ques_type="0";
		            	}
		          	}else{
		            }
		        }else{
		        }
		        if($ques_type==0){
				  $ques_type2=1;
				}
				if($ques_type==1){
					$ques_type2=2;
				}
				$corect_position=array(
					'A' => '0',
					'B' => '1',
					'C' => '2',
					'D' => '3',
					'E' => '4',
					'F' => '5',
					'G' => '6',
					'H' => '7'
				);

				$insert_data = array(
					'banksoal_id' => $banksoal_id,
					'tipe_soal'   => $ques_type2,
					'pertanyaan' => $question
				);

				DB::beginTransaction();

				try {
					$soal = Soal::create($insert_data);

					if($ques_type=="0" || $ques_type=="1"){
						$correct_op=array_filter(explode(',',$singlequestion['correct']));
						$correct_option_position=array();
						foreach($correct_op as $v){
							$correct_option_position[]=$corect_position[trim($v)];
						}

						foreach($singlequestion['option'] as $corect_key => $correct_val){
							if(in_array($corect_key, $correct_option_position)){
								$divideratio=count($correct_option_position);
								$correctoption =1;
							} else {
								$correctoption =0;
							}

							$array = [
								'soal_id' => $soal->id,
								'text_jawaban' => $correct_val,
								'correct' => $correctoption
							];

							JawabanSoal::create($array);
						}
					}

					DB::commit();
				} catch (\Exception $e) {
					DB::rollback();
					return ['success' => false, 'message' => $e->getMessage()];
				}
			}
		}
		return ['success' => true];
	}
}