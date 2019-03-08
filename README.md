<h1 align="center"> captcha </h1>

<p align="center"> a test for captcha.</p>


## Installing

```shell
$ composer require lyignore/captcha -vvv
```

## Usage

```angular2html

use Lyignore\Captcha\NoCaptcha;

$config = [
    'sitekey' => '6LdKdYEUAAAAAJ5Tq5cIDOL0nWag21v9AP1sOU3w',
    'secret'  => '6LdKdYEUAAAAAGCLYLys2JOAbCgWnUIEzeuFwwyW',
    'callBackName' => 'googleCaptchaCallName',  //回调全局函数名称
    'type'  => 'dark',  //dark or light
];

$captcha = new NoCaptcha($config);


//设置验证成功后回调函数的名称，例如叫GoogleVerifiedSuccessfully为回调函数名称，并把返回值放到前端页面中
$captchaSuccFun = $captcha->setCallBack('GoogleVerifiedSuccessfully');


//可设置Google验证框的样式
$captcha->setType('light');


//获取谷歌验证码的前段代码，并且把其放到前端页面中
$captchaHtml = $captcha->getCaptcha();


//Google验证成功后设置的回调函数会收到response,将response通过AJAX传入后台进行验证
$response = '03AOLTBLSiSpVFSVfV6ozdutzzyniL2R-qEDVr_NPuXV70Wq-CeVPRiu0io9rx97wV88CtpQeFoqqmwaMRVYjGvWBUyj_Y2o-eVjH086iA2oV2t2mFCgF4QasjEz1P8R38HKFD9LNmIVHr96kPWnqoFcBHYVNTU0XkmuyyH6Dr-dN6ZjkTtNyfZiwBb8jAXgdQDAqtr1i3jAoc5oaGhv5cyIi1_WfOvqhLUMDv79WcPgUgSaQrGmTCZY9NVnkPhWVBqNE4bHjZAyZx4DszkI4XpQWp3fA3rGdim6g-hHF62rhFklwhiJQkcTyj1rP-Mz_K4OT5TwbubuXi';   //例如收到的response为此段随机字符串


//只能验证一次，判断是否是robot,非robot返回true,第二次验证不管是不是robot都会返回false
$captcha->verifyResponse($response);

```

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/lyignore/captcha/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/lyignore/captcha/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT