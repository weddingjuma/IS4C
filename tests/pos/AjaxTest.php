<?php

use COREPOS\pos\lib\AjaxCallback;
use COREPOS\pos\lib\LocalStorage\WrappedStorage;

use COREPOS\common\mvc\ValueContainer;

/**
 * @backupGlobals disabled
 */
class AjaxTest extends PHPUnit_Framework_TestCase
{
    public function testBase()
    {
        $obj = new AjaxCallback(new WrappedStorage(), new ValueContainer());
        $this->assertEquals('json', $obj->getEncoding());
        $obj->run();

        ob_start();
        CoreLocal::set('cabReference', '1-1-1');
        AjaxCallback::unitTest('COREPOS\\pos\\ajax\\AjaxCabReceipt');
        $output = ob_get_clean();
        $this->assertEquals('Done', $output);
        CoreLocal::set('cabReference', '');
        ob_start();
        AjaxCallback::unitTest('COREPOS\\pos\\ajax\\AjaxDecision');
        $output = ob_get_clean();
        $this->assertEquals('{"dest_page":"gui-modules\\/pos2.php","endorse":false,"cleared":true}', $output);
    }
    
    public function testParser()
    {
        $ajax = new COREPOS\pos\ajax\AjaxParser(new WrappedStorage(), new ValueContainer());
        $ajax->enablePageDrawing(true);
        CoreLocal::set('strRemembered', 'invalidInput');
        CoreLocal::set('msgrepeat', 1);    
        $json = $ajax->ajax();
        $this->assertInternalType('array', $json);
        $this->assertEquals(true, substr($json['main_frame'], -9) == 'login.php');

        CoreLocal::set('strRemembered', 'invalidInput');
        CoreLocal::set('msgrepeat', 1);    
        CoreLocal::set('CashierNo', 1);
        $json = $ajax->ajax();
        $this->assertEquals(false, $json['main_frame']);
        $this->assertEquals('.baseHeight', $json['target']);
        $this->assertNotEquals(0, strlen($json['output']));

        CoreLocal::set('strRemembered', 'CL');
        CoreLocal::set('msgrepeat', 1);
        $json = $ajax->ajax();
        $this->assertNotEquals(false, strstr(json_encode($json), 'pos2.php'));
        CoreLocal::set('CashierNo', '');
    }

    public function testCabReceipt()
    {
        $vals = new ValueContainer();
        $vals->input = '9999-99-1';
        $ajax = new COREPOS\pos\ajax\AjaxCabReceipt(new WrappedStorage(), $vals);
        $this->assertEquals('Done', $ajax->ajax());
    }

    public function testDecision()
    {
        $ajax = new COREPOS\pos\ajax\AjaxDecision(new WrappedStorage(), new ValueContainer());
        $json = $ajax->ajax();
        $this->assertInternalType('array', $json);
        $this->assertEquals(false, $json['endorse']);
        $this->assertEquals(true, $json['cleared']);
        $this->assertEquals('gui-modules/pos2.php', $json['dest_page']);
    }

    public function testEnd()
    {
        $vals = new ValueContainer();
        $vals->receiptType = 'full';
        $vals->ref = '1-1-1';
        $ajax = new COREPOS\pos\ajax\AjaxEnd(new WrappedStorage(), $vals);
        $this->assertEquals(array(), $ajax->ajax());
    }

    public function testEndorse()
    {
        $ajax = new COREPOS\pos\ajax\AjaxEndorse(new WrappedStorage(), new ValueContainer());
        $this->assertEquals('Done', $ajax->ajax());
    }

    public function testScale()
    {
        $ajax = new COREPOS\pos\ajax\AjaxScale(new WrappedStorage(), new ValueContainer());
        $this->assertEquals(' lb', $ajax->ajax());
        $ajax = new COREPOS\pos\ajax\AjaxScale(new WrappedStorage(), new ValueContainer());
        $this->assertInternalType('string', $ajax->ajax());
    }
}

