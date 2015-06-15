<?php

namespace exactSilex\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use ET_Subscriber;
use ET_TriggeredSend;

use GuzzleHttp\Client;
/*use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\Response;*/


class ApiController {

    public function eventAction($event,Request $request,Application $app){
        if ($app['security']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $idUser= $app['security']->getToken()->getUser()->getId();
            $unEvent = $app['dao.event']->findOne($event,$idUser);
            $trigger=$unEvent['triggersend'];



             if($request->request->has('email')){
                $email = $request->request->get('email');

                //Retrieve subscriberkey
                $myclient = $app['exacttarget']->login($app);
                $subscriber = new ET_Subscriber();
                $subscriber->authStub = $myclient;
                $subscriber->props=array('EmailAddress','SubscriberKey');
                $subscriber->filter=array('Property'=>'EmailAddress','SimpleOperator'=>'equals','Value'=>$email);
                $response = $subscriber->get();

             // return var_dump($response);

                if(empty($response->results)){
                    $subscriber = new ET_Subscriber();
                    $subscriber->authStub = $myclient;
                    $subscriber->props = array(
                        "EmailAddress" => $email,
                        "SubscriberKey" => $email
                        );
                    $resultsSub = $subscriber->post();

                    $subKey = $email;   
                }else{
                      $subKey = $response->results[0]->SubscriberKey;
                }

                //Retrieve TriggeredSend
                $triggeredsend = new ET_TriggeredSend();
                $triggeredsend->authStub = $myclient;
                $triggeredsend->props = array('TriggeredSendStatus');
                $triggeredsend->filter = array('Property' => 'CustomerKey','SimpleOperator' => 'equals','Value' => $trigger);
                $responseTrig = $triggeredsend->get();

                    if($responseTrig->results[0]->TriggeredSendStatus != 'Active'){
                        //Set triggeredSendStatus -> Active
                        $triggeredsend = new ET_TriggeredSend();
                        $triggeredsend->authStub = $myclient;
                        $triggeredsend->props = array("CustomerKey" => $trigger, "TriggeredSendStatus"=> "Active");
                        $resultsTrig = $triggeredsend->patch();
                        var_dump($resultsTrig);
                    }


               //Send !
                $triggeredsend = new ET_TriggeredSend();
                $triggeredsend->authStub = $myclient;
                $triggeredsend->props = array("CustomerKey" => $trigger);
                $triggeredsend->subscribers = array(array("EmailAddress"=>$email,"SubscriberKey" => $subKey));
                $results = $triggeredsend->send();

                    if($results->results[0]->StatusCode == 'OK'){
                        return $app->json('Message : SUCCESS ! ');
                    }else{
                            return $app->json('Message : Error ! ');
                    }

             }else{
                    return $app->json('Missing argument ! ',400);
             }
        }else{
                return $app->json('Not connected !');
        }
    }

    public function waveAction(Application $app){

        $access_token = $app['session']->get('access_token');
        $instance_url = $app['session']->get('instance_url');
       
        /*$headers =array();
        $headers[]='Authorization: Bearer '.$access_token;
        $curl2 =curl_init($instance_url.'/services/data/v34.0/wave/datasets');
        curl_setopt($curl2, CURLOPT_HTTPHEADER, $headers);
        $rep = curl_exec($curl2);*/
           
        $client = new Client();

        $request = $client->createRequest('GET', $instance_url.'/services/data/v34.0/wave', [
            'headers' => ['Authorization' => 'Bearer '.$access_token]
        ]);

//$request = $client->createRequest('GET','http://www.google.com');

        $response = $client->send($request);
        $body = $response->getBody();

        return $body;
    }

    public function testAction(Application $app){
          /* if($request->request->has('query')){
                    $data = $request->request->get('query');*/
/*
        $access_token = $app['session']->get('access_token');
        $instance_url = $app['session']->get('instance_url');
     $client = new Client();
          
        $request = $client->createRequest('GET', $instance_url.'/services/data/v34.0/wave/lenses', [
            'headers' => ['Authorization' => 'Bearer '.$access_token]
        ]);

        $response = $client->send($request);
        $body = $response->getBody();
        $data = $response->json();
       // var_dump($data['lenses'][0]['url']);
      //  var_dump($data);

       foreach($data['lenses'] as $l){
         $url_lense =$l['url']; //lense url
         $url_dataset = $l['dataset']['url'];
       // echo $l['assetSharingUrl'];
         echo $url_lense; echo '<br />';
         echo $url_dataset; echo '<br />';
           //var_dump($data);

          }
            $request = $client->createRequest('GET', $instance_url.'/services/data/v34.0/wave/lenses/0FKB00000004HHaOAM', [
            'headers' => ['Authorization' => 'Bearer '.$access_token]]);

        $response = $client->send($request);
        $body = $response->getBody();
        $a = $response->json();
         var_dump($a);


      //  echo $url;
             
          
            
        
        return 'a';*/

       
    }
}