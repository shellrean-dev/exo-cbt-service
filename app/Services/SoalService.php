<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SoalService
{
	public function importQues($question, $banksoal_id)
	{
		$time_offsett = 0;
		foreach($question as $key => $singlequestion){
			if($key != 0){
				$question = $singlequestion['question'];
				$question= str_replace("`",'&#39;',$question);
		        $question= str_replace("‘",'&#39;',$question);
		        $question= str_replace("’",'&#39;',$question);
		        $question= str_replace("â€œ",'&#34;',$question);
		        $question= str_replace("â€˜",'&#39;',$question);

		        $question= str_replace("â€™",'&#39;',$question);
		        $question= str_replace("â€",'&#34;',$question);
				$question= str_replace("'","&#39;",$question);
		        // $question= str_replace("\n","<br>",$question);

		        $option_count=count($singlequestion['option']);
		        $ques_type="0";
		        if($option_count!="0"){
		         	if($singlequestion['correct'] !=""){
		            	if (strpos($singlequestion['correct'],',') !== false) {
		              		$ques_type="4";
		            	}else{
		              		$ques_type="0";
		            	}
		          	}else{
						$ques_type = "6";
		            }
		        }else{
					$ques_type="1";
		        }
		        if($ques_type==0){
				  $ques_type2=1;
				}
				if($ques_type==1){
					$ques_type2=2;
				}
				if($ques_type==4) {
					$ques_type2=4;
				}
				if($ques_type==6) {
					$ques_type2=6;
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

				$soal_id = Str::uuid()->toString();
				$insert_data = array(
					'id' => $soal_id,
					'banksoal_id' => $banksoal_id,
					'tipe_soal'   => $ques_type2,
					'pertanyaan' => "<p>".$question."</p>",
					'created_at'	=> now()->addSeconds($time_offsett),
					'updated_at'	=> now(),
				);

				DB::beginTransaction();

				try {
					DB::table('soals')->insert($insert_data);

					if($ques_type=="0" || $ques_type=="4" || $ques_type == "6"){
						$correct_op=array_filter(explode(',',$singlequestion['correct']));
						$correct_option_position=array();
						foreach($correct_op as $v){
							$correct_option_position[]=$corect_position[trim(strip_tags(html_entity_decode($v)))];
						}

						$jawabans = [];
						$time_offsett_var2 = 0;
                        $label_mark = "A";
						foreach($singlequestion['option'] as $corect_key => $correct_val){
							if(in_array($corect_key, $correct_option_position)){
								$divideratio=count($correct_option_position);
								$correctoption =1;
							} else {
								$correctoption =0;
							}

							$jawabans[] = [
                                'id' => Str::uuid()->toString(),
                                'soal_id' => $soal_id,
                                'text_jawaban' => "<p>" . $correct_val . "</p>",
                                'correct' => $correctoption,
                                'label_mark' => $label_mark++,
                                'created_at' => now()->addSeconds($time_offsett_var2),
                                'updated_at' => now(),
                            ];

							$time_offsett_var2++;
						}
						if(count($jawabans) > 0) {
							DB::table('jawaban_soals')->insert($jawabans);
						}
					}

					DB::commit();
				} catch (\Exception $e) {
					DB::rollback();
					return ['success' => false, 'message' => $e->getMessage()];
				}
			}

			$time_offsett++;
		}
		return ['success' => true];
	}
}
