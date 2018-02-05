<?php

namespace LeavesOvertimeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IncrementAnnualLeavesCommand extends ContainerAwareCommand
{
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('leaves:increment-annual-leaves')
            ->setDescription('Increments the leaves of every employees annually for > 1 year of service at the same date and month of date of hire.');
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        try {
            $entityManager->getRepository('ApplicationSonataUserBundle:User')->incrementBalancesForAnnualAccounts();
            $output->writeln('Script completed successfully!');
        }
        catch (\Exception $e) {
            $output->writeln(sprintf('An exception has occurred: %s', $e->getMessage()));
        }
    }
}
