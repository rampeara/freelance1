<?php

namespace LeavesOvertimeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FreezeLocalBalanceCommand extends ContainerAwareCommand
{
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('leaves:freeze-local-balance')
            ->setDescription('Transfers remaining local balance of employees on probation to frozen local balance at 31 Dec.');
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        try {
            $entityManager->getRepository('ApplicationSonataUserBundle:User')->freezeLocalBalance();
            $output->writeln('Script completed successfully!');
        }
        catch (\Exception $e) {
            $output->writeln(sprintf('An exception has occurred: %s', $e->getMessage()));
        }
    }
}
