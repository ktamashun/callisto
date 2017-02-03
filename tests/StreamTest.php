<?php

namespace Callisto;
use PHPUnit\Framework\TestCase;

/*$r = fopen(__DIR__ . '/stream_logs/fast.log', 'a');
\fwrite($r, "\r\n");
fclose($r);
die();*/

/**
 * Overwrite PHP's built in function to read the test stream.
 *
 * @param $url
 * @param $port
 * @return resource
 */
function fsockopen($url, $port)
{
	return fopen(__DIR__ . '/stream_logs/' . StreamTest::$callistoTestStreamLog, 'r');
}

/**
 * Git won't allow to store \r\n line endings, but the stream has them,
 * so we need to switch the \n\n endings in the test file.
 *
 * @param $handle
 * @param $length
 * @return string
 */
function fread($handle, $length)
{
	$str = \fread($handle, $length);

	if ("\n\n" == substr($str, $length - 2, 2)) {
		$str = substr($str, 0, $length - 2) . "\r\n";
	}

	return $str;
}

/**
 * We will not have to write into the test stream.
 *
 * @param $res
 * @param $str
 * @param $len
 * @return bool
 */
function fwrite($res, $str, $len)
{
	return true;
}

/**
 * Created by PhpStorm.
 * User: tamaskovacs
 * Date: 2017. 01. 31.
 * Time: 0:21
 */
class StreamTest extends TestCase
{
	public static $callistoTestStreamLog;


	public function testConnect()
	{
		self::$callistoTestStreamLog = 'test.log';

		$stream = new Stream(new Oauth(1, 2, 3, 4));
		$stream->connect();

		$statuses = [];
		foreach ($stream->readStream() as $jsonStatus) {
			$statuses[] = json_decode($jsonStatus);
		}

		$this->assertEquals(1, count($statuses));
		$this->assertEquals(826518635371913216, $statuses[0]->id);
	}

	public function testReadMultipleStatuses()
	{
		self::$callistoTestStreamLog = 'multiple_tweets.log';

		$stream = new Stream(new Oauth(1, 2, 3, 4));
		$stream->connect();

		$statuses = [];
		foreach ($stream->readStream() as $jsonStatus) {
			$statuses[] = json_decode($jsonStatus);
		}

		$this->assertEquals(3, count($statuses));
		$this->assertEquals(826518635371913216, $statuses[0]->id);
		$this->assertEquals(826569797970305024, $statuses[1]->id);
		$this->assertEquals(826569798150660096, $statuses[2]->id);
	}

	public function testConnectionError()
	{
		$this->expectException('\Exception');
		self::$callistoTestStreamLog = 'error401.log';

		$stream = new Stream(new Oauth(1, 2, 3, 4));
		$stream->connect();
	}
}
