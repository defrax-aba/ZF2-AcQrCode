<?php
namespace Acelaya\QrCode\Test\View\Helper;

use Acelaya\QrCode\Test\Service\QrCodeServiceMock;
use Acelaya\QrCode\View\Helper\QrCodeHelper;
use PHPUnit\Framework\TestCase;
use Zend\Router\Http\TreeRouteStack;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;

/**
 * Class QrCodeHelperTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class QrCodeHelperTest extends TestCase
{
    /**
     * @var QrCodeHelper
     */
    private $helper;

    public function setUp()
    {
        $config = include __DIR__ . '/../../../config/module.config.php';
        $renderer = new PhpRenderer();
        $renderer->setResolver(new TemplateMapResolver($config['view_manager']['template_map']));
        $router = TreeRouteStack::factory($config['router']);
        $this->helper = new QrCodeHelper($renderer, $router, new QrCodeServiceMock('foobar'));
    }

    public function testAssembleRoute()
    {
        $this->assertStringStartsWith('/qr-code/generate/foobar', $this->helper->assembleRoute('foobar'));
        $this->assertStringStartsWith('/qr-code/generate/barfoo/png', $this->helper->assembleRoute('barfoo', 'png'));
        $this->assertStringStartsWith(
            '/qr-code/generate/anothertext/gif/500',
            $this->helper->assembleRoute('anothertext', 'gif', 500)
        );
    }

    public function testRenderImg()
    {
        $imgText = $this->helper->renderImg('foobar');
        $this->assertStringStartsWith('<img', $imgText);

        $imgText = $this->helper->renderImg('foobar', 'jpg', 200, array(
            'title' => 'This is the title of the image',
            'class' => 'img-thumbnail'
        ));
        $this->assertRegExp('/src="\/qr-code\/generate/', $imgText);
        $this->assertRegExp('/title="This is the title of the image"/', $imgText);
        $this->assertRegExp('/class="img-thumbnail"/', $imgText);
    }

    public function testRenderBase64Img()
    {
        $imgText = $this->helper->renderBase64Img('foobar');
        $this->assertStringStartsWith('<img', $imgText);

        $imgText = $this->helper->renderBase64Img('foobar', 'jpg', 200, array(
            'title' => 'This is the title of the image',
            'class' => 'img-thumbnail'
        ));
        $this->assertRegExp('/title="This is the title of the image"/', $imgText);
        $this->assertRegExp('/class="img-thumbnail"/', $imgText);
        // image/foo is the content type generated by the QrCodeServiceMock
        $this->assertRegExp('/src="data:image\/foo;base64,/', $imgText);
    }

    public function testLastArrayParamIsConsideredAttribs()
    {
        $imgText = $this->helper->renderImg('foobar', array(
            'title' => 'This is the title of the image',
            'class' => 'img-thumbnail'
        ));
        $this->assertRegExp('/title="This is the title of the image"/', $imgText);
        $this->assertRegExp('/class="img-thumbnail"/', $imgText);

        $imgText = $this->helper->renderImg('foobar', 'jpg', array(
                'title' => 'This is the title of the image',
                'class' => 'img-thumbnail'
            ));
        $this->assertRegExp('/title="This is the title of the image"/', $imgText);
        $this->assertRegExp('/class="img-thumbnail"/', $imgText);

        $imgText = $this->helper->renderBase64Img('foobar', array(
                'title' => 'This is the title of the image',
                'class' => 'img-thumbnail'
            ));
        $this->assertRegExp('/title="This is the title of the image"/', $imgText);
        $this->assertRegExp('/class="img-thumbnail"/', $imgText);

        $imgText = $this->helper->renderBase64Img('foobar', 'jpg', array(
                'title' => 'This is the title of the image',
                'class' => 'img-thumbnail'
            ));
        $this->assertRegExp('/title="This is the title of the image"/', $imgText);
        $this->assertRegExp('/class="img-thumbnail"/', $imgText);
    }

    public function testInvoke()
    {
        $this->assertSame($this->helper, $this->helper->__invoke());
        $this->assertStringStartsWith('/qr-code/generate/foobar', $this->helper->__invoke('foobar'));
        $this->assertStringStartsWith('/qr-code/generate/foobar/gif', $this->helper->__invoke('foobar', 'gif'));
        $this->assertStringStartsWith(
            '/qr-code/generate/foobar/gif/432',
            $this->helper->__invoke('foobar', 'gif', 432)
        );
        $this->assertStringStartsWith(
            '/qr-code/generate/foobar/gif/432/25',
            $this->helper->__invoke('foobar', 'gif', 432, 25)
        );
    }

    public function testSetRouteOptions()
    {
        $this->assertEquals($this->helper, $this->helper->setRouteOptions(array()));
    }
}
