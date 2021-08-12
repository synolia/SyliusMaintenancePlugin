<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;

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
                'empty_data' => '',
                'required' => false,
            ])
            ->add('customMessage', TextareaType::class, [
                'label' => 'maintenance.ui.form.custom_message',
                'empty_data' => '',
                'required' => false,
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'maintenance.ui.form.end_date',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'required' => false,
                'constraints' => [
                    new GreaterThan([
                        'propertyPath' => 'parent.all[startDate].data',
                    ]),
                ],
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'maintenance.ui.form.start_date',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'required' => false,
            ])
        ;
    }
}
