<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 6/28/19 9:06 AM
 */

namespace Alamirault\ElasticsearchBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElasticConditionTypeExtension extends AbstractTypeExtension
{

    const EXTENSION_NAME = "elastic_filter_condition";

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (null !== $options[self::EXTENSION_NAME]) {
            $builder->setAttribute(self::EXTENSION_NAME, $options[self::EXTENSION_NAME]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            self::EXTENSION_NAME => null,
        ]);

        $resolver->setAllowedTypes(self::EXTENSION_NAME, ["callable", 'null']);
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return FormType::class;
    }
}