<?php

namespace LeavesOvertimeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FreezeCarryForwardLocalBalanceCommand extends ContainerAwareCommand
{
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('leaves:freeze-carry-forward-local-balance')
            ->setDescription('Transfers remaining carry forward local balance to frozen carry forward balance at 31 March.');
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        try {
            $entityManager->getRepository('ApplicationSonataUserBundle:User')->freezeCarryForwardLocalBalance();
            $output->writeln('Script completed successfully!');
        }
        catch (\Exception $e) {
            $output->writeln(sprintf('An exception has occurred: %s', $e->getMessage()));
        }
    }
}
