<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/3/19 9:47 AM
 */

namespace Alamirault\ElasticsearchBundle\Tests\Form\Extension;

use Alamirault\ElasticsearchBundle\Form\Extension\ElasticConditionTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class ElasticConditionTypeExtensionTest extends TypeTestCase
{
    public function testOptionNotCallable()
    {

        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "elastic_filter_condition" with value true is expected to be of type "callable" or "null", but is of type "boolean".');


        $form = $this->factory->createBuilder(FormType::class)
            ->add("firstname", TextType::class, [
                "elastic_filter_condition" => true,
            ])->getForm();

        $form->submit(null);
    }

    public function testOptionBadReturnCallable()
    {

        $form = $this->factory->createBuilder(FormType::class)
            ->add("firstname", TextType::class, [
                "elastic_filter_condition" => function ($value) {
                    return "myValue";
                },
            ])->getForm();

        $form->submit(null);
        $this->assertTrue($form->isSubmitted() && $form->isValid());
    }

    protected function getTypeExtensions()
    {
        return [
            new ElasticConditionTypeExtension(),
        ];
    }
}