<?php

/*
 * Copyright 2010 Pablo Díez <pablodip@gmail.com>
 *
 * This file is part of Mandango.
 *
 * Mandango is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Mandango is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Mandango. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mandango\Tests\Extension;

use Mandango\Tests\TestCase;

class CoreSingleInheritanceTest extends TestCase
{
    public function testDocumentParentClasses()
    {
        $this->assertTrue(is_subclass_of('Model\TextareaFormElement', 'Model\FormElement'));
        $this->assertTrue(is_subclass_of('Model\RadioFormElement', 'Model\FormElement'));
    }

    public function testDocumentSetDocumentData()
    {
        $formElement = new \Model\FormElement();
        $formElement->setDocumentData(array(
            'label'   => 123,
            'default' => 234,
        ));
        $this->assertSame('123', $formElement->getLabel());
        $this->assertSame(234, $formElement->getDefault());

        $textareaFormElement = new \Model\TextareaFormElement();
        $textareaFormElement->setDocumentData(array(
            'label'   => 234,
            'default' => 345,
        ));
        $this->assertSame('234', $textareaFormElement->getLabel());
        $this->assertSame('345', $textareaFormElement->getDefault());

        $radioFormElement = new \Model\RadioFormElement();
        $radioFormElement->setDocumentData(array(
            'label'   => 345,
            'default' => 'foobar',
            'options' => serialize($options = array('foobar' => 'Foo', 'barfoo' => 'Bar')),
        ));
        $this->assertSame('345', $radioFormElement->getLabel());
        $this->assertSame('foobar', $radioFormElement->getDefault());
        $this->assertSame($options, $radioFormElement->getOptions());
    }

    public function testDocumentSet()
    {
        $document = \Model\TextareaFormElement::create()->set('label', 'foo')->set('default', 'bar');
        $this->assertSame('foo', $document->getLabel());
        $this->assertSame('bar', $document->getDefault());

        $options = array('foo' => 'bar');
        $document = \Model\RadioFormElement::create()->set('label', 'foo')->set('options', $options);
        $this->assertSame('foo', $document->getLabel());
        $this->assertSame($options, $document->getOptions());
    }

    /**
     * @expectedException \invalidArgumentException
     */
    public function testDocumentSetFieldNotExist()
    {
        \Model\RadioFormElement::create()->set('no', 'foo');
    }

    public function testDocumentGet()
    {
        $document = \Model\TextareaFormElement::create()->setLabel('foo')->setDefault('bar');
        $this->assertSame('foo', $document->get('label'));
        $this->assertSame('bar', $document->get('default'));

        $options = array('foo' => 'bar');
        $document = \Model\RadioFormElement::create()->setLabel('foo')->setOptions($options);
        $this->assertSame('foo', $document->get('label'));
        $this->assertSame($options, $document->get('options'));
    }

    /**
     * @expectedException \invalidArgumentException
     */
    public function testDocumentGetFieldNotExist()
    {
        \Model\RadioFormElement::create()->get('no');
    }

    public function testDocumentFromArray()
    {
        $document = \Model\TextareaFormElement::create()->fromArray(array(
            'label'   => 'foo',
            'default' => 'bar',
        ));
        $this->assertSame('foo', $document->getLabel());
        $this->assertSame('bar', $document->getDefault());

        $options = array('foo' => 'bar');
        $document = \Model\RadioFormElement::create()->fromArray(array(
            'label'   => 'foo',
            'options' => $options,
        ));
        $this->assertSame('foo', $document->getLabel());
        $this->assertSame($options, $document->getOptions());
    }

    public function testDocumentToArray()
    {
        $document = \Model\TextareaFormElement::create()->setLabel('foo')->setDefault('bar');
        $this->assertSame(array(
            'label'   => 'foo',
            'default' => 'bar',
        ), $document->toArray());

        $options = array('foo' => 'bar');
        $document = \Model\RadioFormElement::create()->setLabel('foo')->setOptions($options);
        $this->assertSame(array(
            'label'   => 'foo',
            'options' => $options,
        ), $document->toArray());
    }

    public function testDocumentQueryForSave()
    {
        $formElement = \Model\FormElement::create()->setLabel(123)->setDefault(234);
        $this->assertSame(array(
            'label'   => '123',
            'default' => 234,
        ), $formElement->queryForSave());
        $formElement->clearModified();
        $formElement->setId(new \MongoId('123'));
        $this->assertSame(array(), $formElement->queryForSave());

        $textareaFormElement = \Model\TextareaFormElement::create()->setLabel(345)->setDefault(456);
        $this->assertSame(array(
            'type'    => 'textarea',
            'label'   => '345',
            'default' => '456',
        ), $textareaFormElement->queryForSave());
        $textareaFormElement->clearModified();
        $textareaFormElement->setId(new \MongoId('123'));
        $this->assertSame(array(), $textareaFormElement->queryForSave());

        $options = array('foobar' => 'foo', 'barfoo' => 'bar');
        $radioFormElement = \Model\RadioFormElement::create()->setLabel(567)->setDefault(678)->setOptions($options);
        $this->assertSame(array(
            'type'    => 'radio',
            'label'   => '567',
            'default' => 678,
            'options' => serialize($options),
        ), $radioFormElement->queryForSave());
    }

    public function testRepositoryCollectionName()
    {
        $this->assertSame('model_formelement', \Model\FormElement::repository()->getCollectionName());
        $this->assertSame('model_formelement', \Model\TextareaFormElement::repository()->getCollectionName());
        $this->assertSame('model_formelement', \Model\RadioFormElement::repository()->getCollectionName());
    }

    public function testRepositoryCount()
    {
        $formElements = array();
        for ($i = 0; $i < 5; $i++) {
            $formElements[] = \Model\FormElement::create()->setLabel('Element'.$i)->save();
        }

        $textareaFormElements = array();
        for ($i = 0; $i < 3; $i++) {
            $textareaFormElements[] = \Model\TextareaFormElement::create()->setLabel('Textarea'.$i)->save();
        }

        $radioFormElements = array();
        for ($i = 0; $i < 1; $i++) {
            $radioFormElements[] = \Model\RadioFormElement::create()->setLabel('Radio'.$i)->save();
        }

        $this->assertSame(9, \Model\FormElement::repository()->count());
        $this->assertSame(3, \Model\FormElement::repository()->count(array('label' => new \MongoRegex('/^Text/'))));
        $this->assertSame(3, \Model\TextareaFormElement::repository()->count());
        $this->assertSame(0, \Model\TextareaFormElement::repository()->count(array('label' => new \MongoRegex('/^R/'))));
        $this->assertSame(1, \Model\RadioFormElement::repository()->count());
    }

    public function testRepositoryRemove($value='')
    {
        $formElements = array();
        for ($i = 0; $i < 5; $i++) {
            $formElements[] = \Model\FormElement::create()->setLabel('Element'.$i)->save();
        }

        $textareaFormElements = array();
        for ($i = 0; $i < 3; $i++) {
            $textareaFormElements[] = \Model\TextareaFormElement::create()->setLabel('Textarea'.$i)->save();
        }

        $radioFormElements = array();
        for ($i = 0; $i < 1; $i++) {
            $radioFormElements[] = \Model\RadioFormElement::create()->setLabel('Radio'.$i)->save();
        }

        \Model\FormElement::repository()->remove(array('label' => 'Textarea0'));
        $this->assertSame(8, \Model\FormElement::collection()->count());
        \Model\TextareaFormElement::repository()->remove(array('label' => new \MongoRegex('/^Element/')));
        $this->assertSame(8, \Model\FormElement::collection()->count());
        \Model\TextareaFormElement::repository()->remove();
        $this->assertSame(6, \Model\TextareaFormElement::collection()->count());
    }

    public function testQueryAll()
    {
        $formElements = array();
        for ($i = 0; $i < 5; $i++) {
            $formElements[] = \Model\FormElement::create()->setLabel('Element'.$i)->save();
        }

        $textareaFormElements = array();
        for ($i = 0; $i < 5; $i++) {
            $textareaFormElements[] = \Model\TextareaFormElement::create()->setLabel('Textarea'.$i)->save();
        }

        $radioFormElements = array();
        for ($i = 0; $i < 5; $i++) {
            $radioFormElements[] = \Model\RadioFormElement::create()->setLabel('Radio'.$i)->save();
        }

        \Model\FormElement::repository()->getIdentityMap()->clear();
        \Model\TextareaFormElement::repository()->getIdentityMap()->clear();
        \Model\RadioFormElement::repository()->getIdentityMap()->clear();

        // different classes in root class
        $document = \Model\FormElement::query(array('_id' => $formElements[0]->getId()))->one();
        $this->assertInstanceof('Model\FormElement', $document);
        $this->assertEquals($formElements[0]->getId(), $document->getId());
        $document = \Model\FormElement::query(array('_id' => $textareaFormElements[0]->getId()))->one();
        $this->assertInstanceof('Model\TextareaFormElement', $document);
        $this->assertEquals($textareaFormElements[0]->getId(), $document->getId());
        $document = \Model\FormElement::query(array('_id' => $radioFormElements[0]->getId()))->one();
        $this->assertInstanceof('Model\RadioFormElement', $document);
        $this->assertEquals($radioFormElements[0]->getId(), $document->getId());

        // with and without identity map
        $ids = array(
            $formElements[0]->getId(),
            $textareaFormElements[0]->getId(),
            $radioFormElements[0]->getId(),
            $formElements[1]->getId(),
            $textareaFormElements[1]->getId(),
            $radioFormElements[1]->getId(),
        );
        $documents = \Model\FormElement::query(array('_id' => array('$in' => $ids)))->all();
        $this->assertSame(6, count($documents));

        $id = $formElements[0]->getId();
        $this->assertTrue(isset($documents[$id->__toString()]));
        $document = $documents[$id->__toString()];
        $this->assertInstanceof('Model\FormElement', $document);
        $this->assertEquals($id, $document->getId());

        $id = $textareaFormElements[0]->getId();
        $this->assertTrue(isset($documents[$id->__toString()]));
        $document = $documents[$id->__toString()];
        $this->assertInstanceof('Model\TextareaFormElement', $document);
        $this->assertEquals($id, $document->getId());

        $id = $radioFormElements[0]->getId();
        $this->assertTrue(isset($documents[$id->__toString()]));
        $document = $documents[$id->__toString()];
        $this->assertInstanceof('Model\RadioFormElement', $document);
        $this->assertEquals($id, $document->getId());

        $id = $formElements[1]->getId();
        $this->assertTrue(isset($documents[$id->__toString()]));
        $document = $documents[$id->__toString()];
        $this->assertInstanceof('Model\FormElement', $document);
        $this->assertEquals($id, $document->getId());

        $id = $textareaFormElements[1]->getId();
        $this->assertTrue(isset($documents[$id->__toString()]));
        $document = $documents[$id->__toString()];
        $this->assertInstanceof('Model\TextareaFormElement', $document);
        $this->assertEquals($id, $document->getId());

        $id = $radioFormElements[1]->getId();
        $this->assertTrue(isset($documents[$id->__toString()]));
        $document = $documents[$id->__toString()];
        $this->assertInstanceof('Model\RadioFormElement', $document);
        $this->assertEquals($id, $document->getId());

        // no root class
        $document = \Model\TextareaFormElement::query(array('_id' => $id = $textareaFormElements[0]->getId()))->one();
        $this->assertInstanceOf('Model\TextareaFormElement', $document);
        $this->assertEquals($id, $document->getId());
        $document = \Model\TextareaFormElement::query(array('_id' => $formElements[0]->getId()))->one();
        $this->assertNull($document);
        $document = \Model\TextareaFormElement::query(array('_id' => $radioFormElements[0]->getId()))->one();
        $this->assertNull($document);
        $document = \Model\RadioFormElement::query(array('_id' => $formElements[0]->getId()))->one();
        $this->assertNull($document);
    }

    public function testQueryCount()
    {
        $formElements = array();
        for ($i = 0; $i < 5; $i++) {
            $formElements[] = \Model\FormElement::create()->setLabel('Element'.$i)->save();
        }

        $textareaFormElements = array();
        for ($i = 0; $i < 3; $i++) {
            $textareaFormElements[] = \Model\TextareaFormElement::create()->setLabel('Textarea'.$i)->save();
        }

        $radioFormElements = array();
        for ($i = 0; $i < 1; $i++) {
            $radioFormElements[] = \Model\RadioFormElement::create()->setLabel('Radio'.$i)->save();
        }

        $this->assertSame(9, \Model\FormElement::query()->count());
        $this->assertSame(3, \Model\TextareaFormElement::query()->count());
        $this->assertSame(1, \Model\RadioFormElement::query()->count());
    }

    public function testEvents()
    {
        $formElement = \Model\FormElement::create()->setLabel('Element')->save();
        $this->assertSame(array(
            'ElementPreInserting',
            'ElementPostInserting',
        ), $formElement->getEvents());

        $textareaFormElement = \Model\TextareaFormElement::create()->setLabel('Textarea')->save();
        $this->assertSame(array(
            'ElementPreInserting',
            'TextareaPreInserting',
            'ElementPostInserting',
            'TextareaPostInserting',
        ), $textareaFormElement->getEvents());
    }
}