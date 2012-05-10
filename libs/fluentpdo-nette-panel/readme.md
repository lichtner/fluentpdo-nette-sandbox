# FluentPDO Nette Panel

FluentPDO Nette Panel add FluentPDO into Nette Panel

## Installation

	cp FluentPDOPanel.php ....your-nette-project/libs/
	
in `app/config/config.neon` add this lines:

	common:
		...
		nette:
			debugger:
				bar:
					- FluentPDOPanel

in `app/bootstrap.php` somewhere after:

	$container = $configurator->createContainer();

add this lines:

	$container->fluentpdo->debug = function($FluentQuery) {
		FluentPDOPanel::getInstance()->logQuery($FluentQuery);
	};
	
## Licence

Free for commercial and non-commercial use 
([Apache License](http://www.apache.org/licenses/LICENSE-2.0.html) or 
[GPL](http://www.gnu.org/licenses/gpl-2.0.html)).

*Copyright (c) 2012, Marek Lichtner (marek@licht.sk)*
