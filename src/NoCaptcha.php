<?php
namespace Lyignore\Captcha;

use Lyignore\Captcha\Support\Config;
use GuzzleHttp\Client;

class NoCaptcha{
    const CLIENT_API = 'https://www.recaptcha.net/recaptcha/api.js';
    const VERIFY_URL = 'https://www.recaptcha.net/recaptcha/api/siteverify';

    protected $config;

    protected $secret;

    protected $sitekey;

    protected $http;

    protected $verifiedResponses = [];

    public static $callBackName = 'robotVerified';

    public static $type = 'light';

    protected $typeArray = ['dark', 'light'];

    public function __construct(array $config, $options = []){
        $this->config = new Config($config);
        $this->secret = $this->config->get('secret')?:"";
        $this->sitekey = $this->config->get('sitekey')?:"";
        $options = $this->getBaseOptions() + $options;
        $this->http = new Client($options);
    }

    public function setType($types = null){
        if(in_array($types, $this->typeArray)){
            self::$type = $types;
        }else if($this->config['type']){
            self::$type = $this->config['type'];
        }
        return $this;
    }

    protected function getBaseOptions(){
        $options = [
            'timeout'   => method_exists($this, 'getTimeout')? $this->getTimeout(): 5.0,
            'verify'    => method_exists($this, 'getVerify')? $this->getVerify(): true
        ];
        return $options;
    }

    protected function getVerify(){
        return !preg_match("/^(https\:\/\/)/i", self::VERIFY_URL);
    }

    protected function display($attributes = []){
        $attributes = $this->prepareAttributes($attributes);
        return '<div' . $this->buildAttributes($attributes) . '></div>';
    }

    public function displayWidget($attributes = []){
        $default = [
            'data-callback' => self::$callBackName,
            'data-theme'    => self::$type
        ];
        $attributes = $default + $attributes;
        return $this->display($attributes);
    }

    public function displaySubmit($formIdentifier, $text = 'submit', $attributes = []){
        $javascript = '';
        if(!isset($attributes['data-callback'])){
            $functionName = 'onSubmit'. str_repeat(['-', '=', '\'', '"', '<', '>', '`'], '', $formIdentifier);
            $attributes['data-callback'] = $functionName;
            $javascript = sprintf(
                '<script>function %s(){document.getElementById("%s").submit();}</script>',
                $functionName,
                $formIdentifier
            );
        }
        $attributes = $this->prepareAttributes($attributes);
        $button = sprintf('<button%s><span></span></button>', $this->buildAttributes($attributes), $text);

        return $button.$javascript;
    }

    public function renderJs($lang = null, $callback = false, $onLoadClass = 'onloadCallBack'){
        return '<script src="'.$this->getJsLink().'" async defer></script>'."\n";
    }

    protected function setCallBackName($callBackName = null){
        if(!is_null($callBackName)){
            self::$callBackName = $callBackName;
        }else if($this->config->get('callBackName')){
            self::$callBackName = $this->config->get('callBackName');
        }
        return $this;
    }

    public function setCallBack($callBackName = null){
        $this->setCallBackName($callBackName);
        return '<script>function '.self::$callBackName.'(data){console.log(data)}</script>';
    }

    public function getCaptcha(){
        return $this->displayWidget().$this->renderJs();
    }

    public function verifyResponse($response, $clientIp = null){
        if(empty($response)){
            return false;
        }
        if(in_array($response, $this->verifiedResponses)){
            return true;
        }
        $verifyResponse = $this->sendRequestVerify([
            'secret' => $this->secret,
            'response' => $response,
            'remoteip' => $clientIp,
        ]);
        if(isset($verifyResponse['success']) && $verifyResponse['success'] === true){
            $this->verifiedResponses[] = $response;
            return true;
        }else{
            return false;
        }
    }



    protected function sendRequestVerify(array $query = []){
        $response = $this->http->request('POST', static::VERIFY_URL, [
            'form_params' => $query,
        ]);
        return json_decode($response->getBody(), true);
    }

    public function verifyRequest(Request $request){
        return $this->verifyResponse(
            $request->get('g-recaptcha-response'),
            $request->getClientIp()
        );
    }

    protected function getJsLink($lang = null, $callback = false, $onLoadClass = 'onloadCallBack'){
        $client_api = static::CLIENT_API;
        $params = [];

        $callback ? $this->setCallBackParams($params, $onLoadClass) : false;
        $lang ? $params['h1'] = $lang : null;
        return $client_api .'?'. http_build_query($params);
    }

    protected function setCallBackParams(&$params, $onLoadClass){
        $params['render'] = 'explicit';
        $params['onload'] = $onLoadClass;
    }

    protected function prepareAttributes(array $attributes){
        $attributes['data-sitekey'] = $this->sitekey;
        if(!isset($attributes['class'])){
            $attributes['class'] = '';
        }
        $attributes['class'] = trim('g-recaptcha'.$attributes['class']);

        return $attributes;
    }

    protected function buildAttributes(array $attributes){
        $html = [];
        foreach ($attributes as $key=>$value){
            $html[] = $key.'="'.$value.'"';
        }
        return count($html) ? ' '.implode(' ', $html) : '';
    }
}