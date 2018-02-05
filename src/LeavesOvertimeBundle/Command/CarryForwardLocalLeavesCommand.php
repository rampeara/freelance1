<?php

namespace LeavesOvertimeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CarryForwardLocalLeavesCommand extends ContainerAwareCommand
{
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('leaves:carry-forward-local-balance')
            ->setDescription('Transfers local balance to carry forward local balance at 31 Dec.');
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        try {
            $entityManager->getRepository('ApplicationSonataUserBundle:User')->carryForwardLocalBalance();
            $output->writeln('Script completed successfully!');
        }
        catch (\Exception $e) {
            $output->writeln(sprintf('An exception has occurred: %s', $e->getMessage()));
        }
    }
}
