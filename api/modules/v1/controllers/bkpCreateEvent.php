    public function actionCreate(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        $msg = "";
        $statusCode = 200;
        $success = false;
        $ret = [];
        $modelEvent = [];
        $headers = getallheaders();
        $api_token = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');
        if(!$api_token){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
        $token = (new Account)->getBearerToken($api_token);
        $validateToken = (new Account)->validateToken($token);
        if(!$validateToken){
            $msg = "Authentication failed";
            $success = false;
            $ret = [
                'message' => $msg,
                'statusCode' => 200,
                'success' => $success
            ];
            return $ret;
        }
        $user_details = (new Account)->getCusomerDetailsByAPI($token); 
        if(!$user_details){
            Yii::$app->response->statusCode = 401;
            $msg = "Somthing went wrong.";
            $ret = [
                'message' => $msg,
                'statusCode' => 401,
                'success' => $success
            ];
            return $ret;
        }
        $post = Yii::$app->request->post();
        $title = isset($post['title'])?$post['title']:'';
        $date = isset($post['date'])?$post['date']:'';
        $time = isset($post['time'])?$post['time']:'';
        $gift_voucher_ids = isset($post['gift_voucher_ids'])?$post['gift_voucher_ids']:'';
        $menu_ids = isset($post['menu_ids'])?$post['menu_ids']:'';
        $music_file_id = isset($post['music_file_id'])?$post['music_file_id']:'';
        $base64 = isset($post['base_64'])?$post['base_64']:'';
        $image = UploadedFile::getInstanceByName('image');
        $music_file = UploadedFile::getInstanceByName('music_file');
        //$video_file = UploadedFile::getInstanceByName('video_file');
        if($title && $date && $time){
            $model = new Event;
            // $convertedTime = date('h:i:s',strtotime($time));
            $convertedDate = date('Y-m-d',strtotime($date));
            $voucherArray = explode(',',$gift_voucher_ids);
            $menuArray = explode(',',$menu_ids);
            $model->title = $title;
            $model->date = $convertedDate;
            $model->time = $time;
            $model->user_id = $user_details->id;
            if($music_file_id){
                $modelMusic = Music::find()->where(['id'=>$music_file_id])->one();
                if($modelMusic){
                    $model->music_file_url = $modelMusic->music_file_url;
                }
            }
            
            $user = new User;
            $video_file = UploadedFile::getInstanceByName('video_file');
            $videoFilesLocation = Yii::$app->params['upload_path_event_video'];
            if($video_file){

                    $saveFile = $user->uploadAndSave($video_file, $videoFilesLocation);
                    if(isset($saveFile)&&$saveFile!=null){
                        
                        $model->video_file = $saveFile;
                        $model->created_by = isset($post['created_by'])?$post['created_by']:'';
                    }                
                }
            $curl = curl_init();
            $bank=array();
            $body=array(
                    "bank_codes" => ["YESB"],
                    "name"=> $user_details->name,
                    "email"=> $user_details->email,
                    "mobile"=> $user_details->phone_number,
                    "address"=> "some_physical_address",
                    "kyc_verified"=> 1,
                    "kyc_check_decentro"=> 0,
                    "customer_id"=> 'id'.$user_details->id,
                    "upi_onboarding"=>0,
                    );
            curl_setopt_array($curl, array(
                      CURLOPT_URL => 'https://docs.decentro.tech/v2/banking/account/virtual',
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => '',
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 0,
                      CURLOPT_FOLLOWLOCATION => true,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'POST',
                      CURLOPT_POSTFIELDS =>json_encode($body),
                      CURLOPT_HTTPHEADER => array(
                        "Content-Type: application/json",
                        "Accept: */*",
                        "client_id: prezenty_prod",
                        "client_secret: 2YDOXdsxhQazuihuaZTwWI1NK3T7iIXx",
                        "module_secret: Jzf3Ae2CoofEHFmIvDuOailvY7UNUSbT"
                    ),
                ));                        
            $response = curl_exec($curl);
            $response=json_decode($response); 
            curl_close($curl);
            $model->va_bank=$response->data[0]->bank;
            $model->va_number=$response->data[0]->accountNumber;
            $model->va_upi=$response->data[0]->upiId;
            $model->va_ifsc=$response->data[0]->ifsc;            
            $model->save(false);

            // Save event_food_vouchers
            $event_id = $model->id;
            $foodVouchersPostData = isset($post['food_vouchers_ids'])? $post['food_vouchers_ids'] : [];
            $foodVouchers = [];
            foreach($foodVouchersPostData as $fv) {
              $foodVouchers[] = [$event_id, $fv];
            }
            
            if(count($foodVouchers) > 0) {
              Yii::$app->db->createCommand()
                ->batchInsert(EventFoodVoucher::tableName(), ['event_id', 'voucher_id'], $foodVouchers)
                ->execute();
            }

            if($voucherArray){
                foreach($voucherArray as $voucherId){
                    $modelEventGiftVoucher = new EventGiftVoucher;
                    $modelEventGiftVoucher->event_id = $model->id;
                    $modelEventGiftVoucher->gift_voucher_id = $voucherId;
                    $modelEventGiftVoucher->save(false);
                }
            }
            if($menuArray){
                foreach($menuArray as $menuId){
                    $modelEventMenuGift = new EventMenuGift;
                    $modelEventMenuGift->event_id = $model->id;
                    $modelEventMenuGift->menu_gift_id = $menuId;
                    $modelEventMenuGift->save(false);
                }
            }
            $musicFilesLocation = Yii::$app->params['upload_path_music_files'];
            $imageFilesLocation = Yii::$app->params['upload_path_event_images'];
            $user = new User;
            if($image){
                $saveFile = $user->uploadAndSave($image, $imageFilesLocation);
                if(isset($saveFile)&&$saveFile!=null){
                    $model->image_url = $saveFile;
                }
            }
            if($music_file){
                $saveFile = $user->uploadAndSave($music_file, $musicFilesLocation);
                if(isset($saveFile)&&$saveFile!=null){
                    $model->music_file_url = $saveFile;
                }
            }
            if($base64){
                $path = $imageFilesLocation;
                $img = $base64;
                $img = str_replace('data:image/png;base64,', '', $img);
                $img = str_replace(' ', '+', $img);
                $data = base64_decode($img);
                $imageUrl = 'event-image-'.time() . '.jpeg';
                $file = '../../common/uploads/event-images/'.$imageUrl;
                $success = file_put_contents($file, $data);
                $model->image_url = $imageUrl;
            }
            $model->save(false);
            $modelEvent = $model;
            $msg = "Event created successfully";
            $success = true;

            if($user_details->email){

                $body="";
                $appFromEmail="orders@prezentystore.com";
                $mail = Yii::$app->mailer->compose()->setFrom([$appFromEmail => 'Prezenty'])->setTo($user_details->email)->setSubject(' Experience the next level of virtual events!')->setTextBody("Dear ".$user_details->name.",Well done. By signing up You've taken your first step towards a happier and smart life.You have successfully created an event via /n Prezenty.Thanks,Prezenty Team")->send();                
                }
            
        }else{
            $msg = "Title, Date and Time cannot be blank";
        }
        $ret = [
            "baseUrlVideo" => Yii::$app->params['base_path_event_video'],
            "detail" => $modelEvent,
            "message" => $msg,
            "statusCode" => 200,
            "success" => $success          
        ];
        return $ret;
    } 