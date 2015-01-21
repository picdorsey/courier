# Courier
Send transactional email using Postmark directly from ExpressionEngine templates.

- **[Changelog](https://github.com/picdorsey/courier/releases)**
- **[Issues](https://github.com/picdorsey/courier/issues)**

## Requirments

- swiftmailer/swiftmailer: ~5.0
- openbuildings/postmark: 0.2.*

## Installation

- Download the package [here](https://github.com/picdorsey/courier/archive/master.zip) and put it in ```system/third-party/courier``` OR If you use composer in your EE sites, add this to your composer.json file:

```
require: "picdorsey/courier": "dev-master"
```

- run ```composer install``` inside of the courier plugin folder

- Open "pi.courier.php" and add your Postmark API key

## Usage

Place the courier tag inside an EE template to fire an email:

```
{exp:courier:send
    layout="_layouts/email"
    subject="Thank you for shopping with Acme, Inc."
}
	Put some content for your email here...
{/exp:courier:send}
```

If you are using a layout, any content in the above tag pair will be injected into your template, using:

```
[[content]]
```

## Credits

Courier is maintained by [Piccirilli Dorsey](http://picdorsey.com)

## License

[MIT](LICENSE)