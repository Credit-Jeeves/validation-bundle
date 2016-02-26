<?php

namespace RentJeeves\LandlordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ScanningCheckType extends AbstractType
{
    const SCANNING_CHECK_URL = 'https://ssl.selectpayment.com/mp/SingleSignon/Login/Page.aspx';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $timeStamp = new \DateTime();
        $timeStampForHash = clone $timeStamp;

        $hash = $this->getHash($timeStampForHash, $options);

        $builder
            ->add('NetTellerID', 'hidden', ['data' => $options['netTellerId']])
            ->add('CCUrl', 'hidden', ['data' => self::SCANNING_CHECK_URL])
            ->add('CMID', 'hidden', ['data' => $options['CMID']])
            ->add('Metadata', 'hidden', ['data' => $options['Metadata']])
            ->add('TimeStamp', 'hidden', ['data' => $timeStamp->format('m/d/Y h:i:s A')])
            ->add('Secret', 'hidden', ['data' => $options['secret']])
            ->add('Hash', 'hidden', ['data' => $hash])
            ->setAction(self::SCANNING_CHECK_URL)
            ->setMethod('POST');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(['netTellerId', 'secret', 'CMID'])
            ->setDefaults(
                [
                    'csrf_protection' => false,
                    'Metadata' => '',
                    'ReferringApplication' => 'NetTeller',
                ]
            );
    }

    /**
     * @param \DateTime $dateTime
     * @param array     $options
     *
     * @return string
     */
    protected function getHash(\DateTime $dateTime, array $options)
    {
        $string = sprintf(
            '%s%s%s%s%s',
            $options['netTellerId'],
            $options['CMID'],
            $options['Metadata'],
            $dateTime->format('m/d/y H:i:s'),
            $options['secret']
        );

        $hashInLowerCase = sha1($string);
        $hash = strtoupper($hashInLowerCase);

        return $hash;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'scanning_check';
    }
}
