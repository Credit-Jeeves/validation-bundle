<?php
namespace CreditJeeves\ExperianBundle\Tests\Converter;

use CreditJeeves\CoreBundle\Tests\BaseTestCase;
use CreditJeeves\DataBundle\Enum\AtbType;
use CreditJeeves\ExperianBundle\Converter\Atb as Converter;
use CreditJeeves\DataBundle\Entity\Atb as Entity;
use CreditJeeves\ExperianBundle\Model\Atb as Model;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class AtbCase extends BaseTestCase
{
    /**
     * @test
     */
    public function itMustBeConstructed()
    {
        $trans = $this->getMock(
            'Symfony\Bundle\FrameworkBundle\Translation\Translator',
            array(
                'trans'
            ),
            array(),
            '',
            false
        );
        new Converter($trans, array());
    }

    protected function getModelObject()
    {
        $model = new Model();
        $model->setSimType(102)
            ->setSimTypeGroup('10x')
            ->setScoreCurrent(600)
            ->setScoreTarget(900)
            ->setCashUsed(10)
            ->setInput(100)
            ->setIsDealerSide(false)
            ->setMessage('message')
            ->setScoreBest(700)
            ->setScoreInit(600)
            ->setType(AtbType::CASH)
            ->setTitle('cash-increase-title-message-%POINTS_INCREASE%-%STEPS%-%CASH_USED%-%SCORE_BEST%')
            ->setTitleMessage('cash-increase-title-sub-message')
            ->setBlocks(
                array(
                    array(
                        'message' => '10x-message-%TR_SUBNAME%-%TR_ACCTNUM%-%CASH_DIFF%-%TR_BALANCE%-%TR_AMOUNT1%',
                        'sub_message' => '10x-sub-message-%TR_SUBNAME%-%SUBSCRIBER_PHONE_NUMBER%',
                        'links' => array(
                            array(
                                'text' => 'link-learn-more',
                                'url' => 'http://user_voice/knowledgebase/articles/133308-pay-down-your-balances',
                            )
                        ),
                    )
                )
            );
        return $model;
    }

    /**
     * @test
     */
    public function getModel()
    {
        $converter = $this->getMock(
            '\CreditJeeves\ExperianBundle\Converter\Atb',
            array(
                'trans',
                'getExternalUrl',
            ),
            array(),
            '',
            false
        );
        $converter->expects($this->any())
            ->method('trans')
            ->will($this->returnArgument(0));
        $converter->expects($this->any())
            ->method('getExternalUrl')
            ->will($this->returnArgument(0));

        $entity = new Entity();
        $entity->setResult(
            array(
                'sim_type' => 102,
                'cash_used' => 10,
                'message' => 'message',
                'score_best' => 700,
                'score_init' => 600,
                'transaction_signature' => '123',
                'blocks' => array(
                    array(
                        'tr_subname' => '',
                        'tr_acctnum' => '',
                        'arf_balance' => '',
                        'tr_balance' => '',
                        'subscriber_phone_number' => '',
                    )
                )
            )
        );
        $entity->setInput(100);
        $entity->setType(AtbType::CASH);
        $entity->setScoreCurrent(600);
        $entity->setScoreTarget(900);



        /* @var $converter Converter */
        $result = $converter->getModel($entity);

        $this->assertEquals($this->getModelObject(), $result);

    }

    /**
     * @test
     */
    public function jsonFromModel()
    {
        /** @var $serializer \JMS\Serializer\Serializer */
        $serializer = self::getContainer()->get('jms_serializer');

        $expects = <<<JSON
{
    "is_dealer_side": false,
    "type": "cash",
    "input": 100,
    "score_init": 600,
    "score_best": 700,
    "score_current": 600,
    "score_target": 900,
    "cash_used" :10,
    "sim_type": 102,
    "sim_type_group": "10x",
    "message": "message",
    "blocks": [
        {
            "message": "10x-message-%TR_SUBNAME%-%TR_ACCTNUM%-%CASH_DIFF%-%TR_BALANCE%-%TR_AMOUNT1%",
            "sub_message": "10x-sub-message-%TR_SUBNAME%-%SUBSCRIBER_PHONE_NUMBER%",
            "links": [
                {
                    "text": "link-learn-more",
                    "url": "http:\/\/user_voice\/knowledgebase\/articles\/133308-pay-down-your-balances"
                }
            ]
        }
    ],
    "title": "cash-increase-title-message-%POINTS_INCREASE%-%STEPS%-%CASH_USED%-%SCORE_BEST%",
    "title_message": "cash-increase-title-sub-message"
}
JSON;


        $result = $serializer->serialize($this->getModelObject(), 'json');

        $this->assertEquals(str_replace(array(' ', "\n"), '', $expects), $result);
    }
}
