<?php

/*
 * This file is part of the Roadie package.
 *
 * (c) Vincent Touzet <vincent.touzet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Roadie\Modifier\Symfony;

use Roadie\Modifier\Symfony\AppKernelModifier;

class AppKernelModifierTest extends \PHPUnit_Framework_TestCase
{
    public function testRegisterBundle()
    {
        $data = 'VinceT\BootstrapFormBundle\VinceTBootstrapFormBundle';

        $new = $this->modify($data);

        $this->assertTrue(strstr($new, 'new VinceT\BootstrapFormBundle\VinceTBootstrapFormBundle(),') !== false);
    }

    public function testRegisterBothEnvironments()
    {
        $data = [
            'all' => 'VinceT\BootstrapFormBundle\VinceTBootstrapFormBundle',
            'dev' => 'VinceT\BaseBundle\VinceTBaseBundle',
        ];

        $new = $this->modify($data);

        $this->assertTrue(strstr($new, 'new VinceT\BootstrapFormBundle\VinceTBootstrapFormBundle(),') !== false);
        $this->assertTrue(strstr($new, '$bundles[] = new VinceT\BaseBundle\VinceTBaseBundle();') !== false);
    }

    public function testRegisterWithArguments()
    {
        $data = 'VinceT\BootstrapFormBundle\VinceTBootstrapFormBundle($arg1, $arg2)';

        $new = $this->modify($data);

        $this->assertTrue(strstr($new, 'new VinceT\BootstrapFormBundle\VinceTBootstrapFormBundle($arg1, $arg2),') !== false);
    }

    public function testRegisterDev()
    {
        $data = [
            'dev' => 'VinceT\BootstrapFormBundle\VinceTBootstrapFormBundle',
        ];

        $new = $this->modify($data);

        $this->assertTrue(strstr($new, '$bundles[] = new VinceT\BootstrapFormBundle\VinceTBootstrapFormBundle();') !== false);
    }

    public function testRegisterMultiple()
    {
        $data = [
            'Sonata\CoreBundle\SonataCoreBundle',
            'Sonata\BlockBundle\SonataBlockBundle',
            'Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle',
            'Sonata\AdminBundle\SonataAdminBundle',
        ];

        $new = $this->modify($data);

        $this->assertTrue(strstr($new, 'new Sonata\CoreBundle\SonataCoreBundle(),') !== false);
        $this->assertTrue(strstr($new, 'new Sonata\BlockBundle\SonataBlockBundle(),') !== false);
        $this->assertTrue(strstr($new, 'new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),') !== false);
        $this->assertTrue(strstr($new, 'new Sonata\AdminBundle\SonataAdminBundle(),') !== false);
    }

    public function testRegisterMultipleBothEnvironments()
    {
        $data = [
            'all' => [
                'Sonata\CoreBundle\SonataCoreBundle',
                'Sonata\BlockBundle\SonataBlockBundle',
                'Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle',
                'Sonata\AdminBundle\SonataAdminBundle',
            ],
            'dev' => [
                'VinceT\BootstrapFormBundle\VinceTBootstrapFormBundle',
                'VinceT\BaseBundle\VinceTBaseBundle',
            ],
        ];

        $new = $this->modify($data);

        $this->assertTrue(strstr($new, 'new Sonata\CoreBundle\SonataCoreBundle(),') !== false);
        $this->assertTrue(strstr($new, 'new Sonata\BlockBundle\SonataBlockBundle(),') !== false);
        $this->assertTrue(strstr($new, 'new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),') !== false);
        $this->assertTrue(strstr($new, 'new Sonata\AdminBundle\SonataAdminBundle(),') !== false);
        $this->assertTrue(strstr($new, '$bundles[] = new VinceT\BootstrapFormBundle\VinceTBootstrapFormBundle();') !== false);
        $this->assertTrue(strstr($new, '$bundles[] = new VinceT\BaseBundle\VinceTBaseBundle();') !== false);
    }

    private function modify($data)
    {
        $tmp_file = tempnam(sys_get_temp_dir(), uniqid());
        $content = <<<EOL
<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        \$bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new AppBundle\AppBundle(),
        ];

        if (in_array(\$this->getEnvironment(), ['dev', 'test'], true)) {
            \$bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            \$bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            \$bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            \$bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return \$bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.\$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface \$loader)
    {
        \$loader->load(\$this->getRootDir().'/config/config_'.\$this->getEnvironment().'.yml');
    }
}

EOL;
        file_put_contents($tmp_file, $content);

        $modifier = new AppKernelModifier($tmp_file, $data);
        $modifier->modify();

        $new = file_get_contents($tmp_file);

        unlink($tmp_file);

        return $new;
    }
}
