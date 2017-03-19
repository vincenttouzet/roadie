<?php

/*
 * This file is part of the Roadie package.
 *
 * (c) Vincent Touzet <vincent.touzet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Roadie\Modifier;

use Roadie\Modifier\YAMLModifier;
use Symfony\Component\Yaml\Yaml;

class YAMLModifierTest extends \PHPUnit_Framework_TestCase
{
    public function testUpdateExistingString()
    {
        $original = <<<YAML
_wdt:
    prefix: /_wdt
YAML;

        $update = [
            '_wdt' => [
                'prefix' => '/new-prefix',
            ],
        ];

        $new = $this->modify($original, $update);

        $this->assertEquals('/new-prefix', $new['_wdt']['prefix'], 'Prefix must be set to "/new-prefix"');
    }

    public function testUpdateExistingBoolean()
    {
        $original = <<<YAML
twig:
    debug: false
YAML;

        $update = [
            'twig' => [
                'debug' => true,
            ],
        ];

        $new = $this->modify($original, $update);

        $this->assertTrue($new['twig']['debug'], 'Debug must be true');
    }

    public function testUpdateExistingMultiLineValueBySingle()
    {
        $original = <<<YAML
path:
    to: |
        multi
        line
        value
        
YAML;

        $update = [
            'path' => [
                'to' => 'Single line value',
            ],
        ];

        $new = $this->modify($original, $update);

        $this->assertEquals('Single line value', $new['path']['to'], 'Multi-line value must be removed');
    }

    public function testAddToCollection()
    {
        $original = <<<YAML
path:
    to: 
        - value 1
        - value 2
        
YAML;

        $update = [
            'path' => [
                'to' => [
                    'value 3',
                ],
            ],
        ];

        $new = $this->modify($original, $update);

        $this->assertEquals('value 1', $new['path']['to'][0], 'Multi-line value must be removed');
        $this->assertEquals('value 2', $new['path']['to'][1], 'Multi-line value must be removed');
        $this->assertEquals('value 3', $new['path']['to'][2], 'Multi-line value must be removed');
    }

    public function testOverrideCollection()
    {
        $original = <<<YAML
path:
    to: 
        - value 1
        - value 2
        
YAML;

        $update = [
            'path' => [
                'to' => [
                    'ITEM_COLLECTION_OVERRIDE',
                    'value 3',
                ],
            ],
        ];

        $new = $this->modify($original, $update);

        $this->assertEquals('value 3', $new['path']['to'][0], 'Multi-line value must be removed');
    }

    public function testAddNewBlock()
    {
        $original = <<<YAML
path:
    to: value
        
YAML;

        $update = [
            'new' => [
                'value' => 'test',
            ],
        ];

        $new = $this->modify($original, $update);

        $this->assertEquals('test', $new['new']['value'], 'new.value must equals test');
    }

    public function testAddNewSubBlock()
    {
        $original = <<<YAML
path:
    to: value
        
YAML;

        $update = [
            'path' => [
                'sub' => 'test',
            ],
        ];

        $new = $this->modify($original, $update);

        $this->assertEquals('test', $new['path']['sub'], 'path.sub must equals test');
    }

    /**
     * @expectedException \Roadie\Modifier\Exception\AlreadyModifiedException
     */
    public function testExceptionOnsetDataAfterModify()
    {
        $original = <<<YAML
_wdt:
    prefix: /_wdt
YAML;
        $tmp_file = tempnam(sys_get_temp_dir(), uniqid());

        file_put_contents($tmp_file, $original);

        $modifier = new YAMLModifier($tmp_file, [
            '_wdt' => [
                'test' => 'test',
            ],
        ]);
        $modifier->modify();
        unlink($tmp_file);

        $modifier->setData(['']);
    }

    /**
     * @param $yaml
     * @param $data
     *
     * @return string
     */
    protected function modify($yaml, $data)
    {
        $tmp_file = tempnam(sys_get_temp_dir(), uniqid());

        file_put_contents($tmp_file, $yaml);

        $modifier = new YAMLModifier($tmp_file, $data);
        $modifier->modify();

        $new = file_get_contents($tmp_file);

        unlink($tmp_file);

        return Yaml::parse($new);
    }
}
