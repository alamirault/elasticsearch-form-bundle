<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/3/19 9:34 AM
 */

namespace Alamirault\ElasticsearchBundle\Tests\Form\Extension;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class FormWithoutElasticConditionTypeExtensionTest extends TypeTestCase
{
    public function testElasticFilterConditionOptionWithoutExtension()
    {
        $this->expectException(UndefinedOptionsException::class);
        $this->expectExceptionMessageRegExp('/^The option "elastic_filter_condition" does not exist./');

        $form = $this->factory->createBuilder(FormType::class)
            ->add("firstname", TextType::class, [
                "elastic_filter_condition" => true,
            ])->getForm();

        $form->submit(null);
        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());

    }
}