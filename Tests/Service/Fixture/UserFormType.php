<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/3/19 12:49 PM
 */

namespace Alamirault\ElasticsearchBundle\Tests\Service\Fixture;

use Elastica\Query\Match;
use Elastica\Query\Wildcard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add("name", TextType::class, [
            "elastic_filter_condition" => function (?string $value) {
                if (is_null($value)) {
                    return;
                }

                return new Match('name', $value);
            },
            "data" => "Jean",
        ])
            ->add("email", TextType::class, [
                "elastic_filter_condition" => function (?string $value) {
                    if (is_null($value)) {
                        return;
                    }

                    return new Wildcard('email', $value . "*");
                },
            ]);
    }

}