<?php

use Nette\Database\Helpers,
	Nette\Diagnostics\Debugger;

/**
 * Debug panel for FluentPDO.
 * 
 * This panel is based on Nette Class \Nette\Database\Diagnostics\ConnectionPanel
 *
 * @author Marek Lichtner (marek@licht.sk)
 */
class FluentPDOPanel implements Nette\Diagnostics\IBarPanel
{
	/** @var int maximum SQL length */
	static public $maxLength = 0;

	/** @var int logged time */
	private $totalTime = 0;

	/** @var array */
	private $queries = array();

	/** @var string */
	public $name;

	/** @var bool|string explain queries? */
	public $explain = FALSE;

	/** @var bool */
	public $disabled = FALSE;

	/**
	 * @var FluentPDO\Panel
	 */
	private static $_instance = null;

	/**
	 * Instantiate using {@link getInstance()}; 
	 *
	 * @return void
	 */
	public function __construct()
	{
		self::$_instance = $this;
	}

	/**
	 * Create singleton instance
	 *
	 * @return Diggriola\Panel
	 */
	public static function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	public function getId()
	{
		return 'FluentPDO';
	}

	public function logQuery(\FluentQuery $fquery)
	{
		if ($this->disabled) {
			return;
		}
		$source = NULL;
		foreach (/*5.2*PHP_VERSION_ID < 50205 ? debug_backtrace() : */debug_backtrace(FALSE) as $row) {
			if (isset($row['file']) && is_file($row['file']) && strpos($row['file'], NETTE_DIR . DIRECTORY_SEPARATOR) !== 0) {
				if (strpos($row['file'], 'FluentPDO.php') !== FALSE) continue;
				if (isset($row['function']) && strpos($row['function'], 'call_user_func') === 0) continue;
				if (isset($row['function']) && strpos($row['function'], 'logQuery') === 0) continue;
				if (isset($row['function']) && strpos($row['function'], 'debugger') === 0) continue;
				if (isset($row['function']) && strpos($row['function'], 'debugger') === 0) continue;
				$source = array($row['file'], (int) $row['line']);
				break;
			}
		}
		$this->totalTime += $fquery->getTime();
		$this->queries[] = array(
			$fquery->getQuery(), 
			$fquery->getParameters(), 
			$fquery->getTime(), 
			$fquery->getResult()->rowCount(), 
			$fquery->getPDO(), 
			$source
		);
	}



	public static function renderException($e)
	{
		if ($e instanceof \PDOException && isset($e->queryString)) {
			return array(
				'tab' => 'SQL',
				'panel' => Helpers::dumpSql($e->queryString),
			);
		}
	}



	public function getTab()
	{
		return '<span title="FluentPDO ' . htmlSpecialChars($this->name) . '">'
			. '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9wDCAg6E4Q46tQAAAJ0SURBVDjLjZNPiNR1GMY/v5ndmUqiZuvQbkWwmp3SAhGJIDoEqR0SPESHTp2CKCjMgx4DRbpIEEFEdij6AyIGkuWhJA+RluWILNElnVp1Zmt0nPm+fztsO7tLRD3n9/3wvM/7vlVmsuetj78f9ocPAdTr1cLF850KSHdvDXrXkDJy09LTUn4/eerdDaxQlZm8sv9QbtqyhQi48Zvh7gyGQwCKFNwcD+XYh4d+aEw2HwYoj51BRkptieSauDpaFBFDVBAVTAXVgqVhqnXRgmhh65NbkaJMLAEigqwl59vfLfR6V+lemifcW8PBNUSLutnFCMF0sV5cULFFgJsTBBnJKKUFVPyLIgSATz84jOnfAB05HkmG40V54pntfPP5Sd5544Vx43sbduyujezAc3Of+UpgDcA0yHAiAzNbDK3ouGjHzr2o27MS8vb7659a5WgCwIphGaQnd83eyYlPjsUDYX/sf/S1Vn2iYtuVNvfdsZb6UB9sz5+7Dry8GqDGTZMNcjJZf88m1k57de9CvzU1K5ThkNtyI5mOq7HuRHlp38zm67s73+4ZjxBFxpbGaxRBVWg2m0QYEUpm4i6oe2PfzOZlByrLIV64cIZet8vcr52Fmzv91prmLdza/YnZqVnqBc5ebb8J7FL3ZYBLGYd4w0Z0L3Uat4OenV7TeKSXAnDucvvHqsRpKl4E2Dt/euUWjMu/9Lny85+LV2nWmEsHkFNTFUfuXoeEflRy9HyVtXHzMsCMiCAISn8AMJieuR8yOX78IO4K8DrgJUf845m2bX813Rx3Jdx5/Omd1ddHj/DFlwf5Ly39wqrT/eroYcKd/6O/AFLprNI4HkOhAAAAAElFTkSuQmCC" />'
			. count($this->queries) . ' queries'
			. ($this->totalTime ? ' / ' . sprintf('%0.1f', $this->totalTime * 1000) . 'ms' : '')
			. '</span>';
	}



	public function getPanel()
	{
		$this->disabled = TRUE;
		$s = '';
		$h = 'htmlSpecialChars';
		foreach ($this->queries as $i => $query) {
			list($sql, $params, $time, $rows, $connection, $source) = $query;

			$explain = NULL; // EXPLAIN is called here to work SELECT FOUND_ROWS()
			if ($this->explain && preg_match('#\s*\(?\s*SELECT\s#iA', $sql)) {
				try {
					$cmd = is_string($this->explain) ? $this->explain : 'EXPLAIN';
					$explain = $connection->queryArgs("$cmd $sql", $params)->fetchAll();
				} catch (\PDOException $e) {}
			}

			$s .= '<tr><td>' . sprintf('%0.3f', $time * 1000);
			if ($explain) {
				static $counter;
				$counter++;
				$s .= "<br /><a href='#' class='nette-toggler' rel='#nette-DbConnectionPanel-row-$counter'>explain&nbsp;&#x25ba;</a>";
			}

			$s .= '</td><td class="nette-DbConnectionPanel-sql">' . Helpers::dumpSql(self::$maxLength ? Nette\Utils\Strings::truncate($sql, self::$maxLength) : $sql);
			if ($explain) {
				$s .= "<table id='nette-DbConnectionPanel-row-$counter' class='nette-collapsed'><tr>";
				foreach ($explain[0] as $col => $foo) {
					$s .= "<th>{$h($col)}</th>";
				}
				$s .= "</tr>";
				foreach ($explain as $row) {
					$s .= "<tr>";
					foreach ($row as $col) {
						$s .= "<td>{$h($col)}</td>";
					}
					$s .= "</tr>";
				}
				$s .= "</table>";
			}
			if ($source) {
				$s .= Nette\Diagnostics\Helpers::editorLink($source[0], $source[1])->class('nette-DbConnectionPanel-source');
			}

			$s .= '</td><td>';
			foreach ($params as $param) {
				$s .= Debugger::dump($param, TRUE);
			}

			$s .= '</td><td>' . $rows . '</td></tr>';
		}

		return empty($this->queries) ? '' :
			'<style> #nette-debug td.nette-DbConnectionPanel-sql { background: white !important }
			#nette-debug .nette-DbConnectionPanel-source { color: #BBB !important } </style>
			<h1 style="font-size: 120%; font-weight: bold">FluentPDO Queries: ' . count($this->queries) . ($this->totalTime ? ', time: ' . sprintf('%0.3f', $this->totalTime * 1000) . ' ms' : '') . '</h1>
			<div class="nette-inner nette-DbConnectionPanel">
			<table>
				<tr><th>Time&nbsp;ms</th><th>SQL Statement</th><th>Params</th><th>Rows</th></tr>' . $s . '
			</table>
			</div>';
	}

}
