# yii2-crossbar-http

This extension allows to submit PubSub and CallReg events via HTTP/POST requests to a [Crossbar HTTP Publisher](http://crossbar.io/docs/HTTP-Bridge-Publisher/) and [Crossbar HTTP Caller](http://crossbar.io/docs/HTTP-Bridge-Caller/).

[Crossbar.io](http://crossbar.io/) is a networking platform for distributed and microservice applications, implementing the open [Web Application Messaging Protocol (WAMP)](http://wamp-proto.org/). It is feature rich, scalable, robust and secure. Let Crossbar.io take care of the hard parts of messaging so you can focus on your app's features. 

Inspired from [Symfony/Crossbar HTTP Publisher Bundle](https://github.com/facile-it/crossbar-http-publisher-bundle) by [peelandsee](https://github.com/peelandsee).

####Supports:

* [Signed requests](http://crossbar.io/docs/HTTP-Bridge-Publisher/#signed-requests)
* SSL certificate verification skip (useful in dev enviroment)
* Proxy usage if needed

####Requires:

* php: >=5.4
* yii2: >=2.0.1
* yiisoft/yii2-httpclient: >= 2.0.1

####Installation

As simple as download it 

```console
$ composer require macfly/yii2-crossbar-http "1.*"
```

or add

```json
"macfly/yii2-crossbar-http: "1.*"
```

to the `require` section of your composer.json.

## Configuring application

After extension is installed you need to setup application component:

```php
return [
    'components' => [
        'crossbarhttp' => [
            'class'					=> 'yii\crossbar\Http',
			      'url'						=> 'http://127.0.0.1:8080', // Crossbar router url
			      'key'						=> 'mykey',									// Key if signed request is used (optionel)
			      'secret'				=> 'my secret',							// Secret if signed request is used (optional)
						'timeout'				=> 5,												// Conenction timeout (default: 5 seconds)
						'sslVerifyPeer'	=> true,										// Check ssl certificate (default: true)
						'proxy'					=> 'tcp://ip:port/'					// Proxy to use to access url (optional)
                // etc.
            ],
        ]
        // ...
    ],
    // ...
];
```

## Usage

Publish on the crossbar router in your controller, or model:

````php
$topic = 'com.myapp.topic1';

// using args
Yii::$app->crossbarhttp->publish('/publisher', $topic, ['foo', 1]);

// using kwargs
Yii::$app->crossbarhttp->publish('/publisher', $topic, null, ['key'=>'value']);

// using both and printing Crossbar's response already decoded (WAMPReply Object):
$rp = Yii::$app->crossbarhttp->publish('/publisher', $topic, ['foo',1], ['key'=>'value']);

// ouptuts:

if($rp->isError()) {	// True if WAMP error
	$rp->error 		 			// Get error detail
} else
	$rp->kwargs
	$rp->args
}

````
