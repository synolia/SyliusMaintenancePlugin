<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MaintenanceConfigurationType extends AbstractType
{
    private TranslatorInterface $translator;

    private FlashBagInterface $flashBag;

    public function __construct(TranslatorInterface $translator, FlashBagInterface $flashBag)
    {
        $this->translator = $translator;
        $this->flashBag = $flashBag;
    }

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
            ->add('customMessage', TextareaType::class, [
                'label' => 'maintenance.ui.form.custom_message',
                'required' => false,
            ])
            ->add('startDate', DatetimeType::class, [
                'label' => 'maintenance.ui.form.start_date',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'required' => false,
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'maintenance.ui.form.end_date',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'ui icon primary button'],
                'label' => 'maintenance.ui.form.validate',
            ])
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
                $form = $event->getForm();
                $startDate = $form->get('startDate')->getData();
                $endDate = $form->get('endDate')->getData();
                $message = $this->translator->trans('maintenance.ui.form.error_end_date');
                if (null !== $endDate && $startDate > $endDate) {
                    $form->get('endDate')->addError(new FormError($message));
                }
                $this->flashBag->add('error', $message);
            })
        ;
    }
}
