<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class MaintenanceConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enabled', CheckboxType::class, [
                'label' => 'maintenance.ui.form.enabled',
                'required' => true,
            ])
            ->add('ipAddresses', TextType::class, [
                'label' => 'maintenance.ui.form.ip',
                'attr' => [
                    'placeholder' => 'maintenance.ui.form.placeholder',
                ],
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'ui icon primary button'],
                'label' => 'maintenance.ui.form.validate',
            ])
        ;
    }
}
