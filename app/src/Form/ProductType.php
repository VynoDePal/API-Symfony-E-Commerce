<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du produit',
                'required' => true,
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix du produit',
                'currency' => 'XOF',
                'required' => true,
            ])
            ->add('photo', VichFileType::class, [
                'label' => 'Photo du produit',
                'required' => true,
                'allow_delete' => true,
                'download_uri' => true,
                'asset_helper' => true,
            ])
            ->add('isAvailable', null, [
                'label' => 'Disponible en stock',
                'required' => false,
            ])
            ->add('stripeProductId', null, [
                'label' => false,
                'required' => false,
                'attr' => ['style' => 'display:none;'],
            ])
            ->add('stripePriceId', null, [
                'label' => false,
                'required' => false,
                'attr' => ['style' => 'display:none;'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'csrf_protection' => false,
        ]);
    }
}
