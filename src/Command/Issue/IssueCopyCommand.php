<?php

/*
 * This file is part of Gush package.
 *
 * (c) Luis Cordova <cordoval@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Gush\Command\Issue;

use Gush\Command\BaseCommand;
use Gush\Feature\IssueTrackerRepoFeature;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Copy an issue from one repository to another.
 */
class IssueCopyCommand extends BaseCommand implements IssueTrackerRepoFeature
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('issue:copy')
            ->setDescription('Copy issue')
            ->addArgument('issue_number', InputArgument::REQUIRED, 'Issue number to move')
            ->addArgument('target_username', InputArgument::REQUIRED, 'Target username or organization')
            ->addArgument('target_repository', InputArgument::REQUIRED, 'Target repository')
            ->addOption('prefix', null, InputOption::VALUE_REQUIRED, 'Prefix for the issue title')
            ->addOption('close', null, InputOption::VALUE_NONE, 'Close original issue')
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command moves an issue from one repository to another

EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $issueNumber = $input->getArgument('issue_number');
        $targetUsername = $input->getArgument('target_username');
        $targetRepository = $input->getArgument('target_repository');
        $prefix = $input->getOption('prefix') ?: '';
        $close = $input->getOption('close');

        $adapter = $this->getIssueTracker();

        $srcIssue = $adapter->getIssue($issueNumber);
        $srcTitle = $prefix.$srcIssue['title'];

        $srcUsername = $adapter->getUsername();
        $srcRepository = $adapter->getRepository();

        $adapter->setUsername($targetUsername);
        $adapter->setRepository($targetRepository);

        $issueUrl = $adapter->getIssueUrl(
            $adapter->openIssue(
                $srcTitle,
                $srcIssue['body'],
                $srcIssue
            )
        );

        $this->getHelper('gush_style')->success(
            sprintf('Opened issue: %s', $issueUrl)
        );

        $adapter->setUsername($srcUsername);
        $adapter->setRepository($srcRepository);

        if (true === $close) {
            if ('closed' === $srcIssue['state']) {
                $this->getHelper('gush_style')->error(
                    sprintf(
                        'Issue #%d was already closed.',
                        $issueNumber
                    )
                );
            } else {
                $adapter->closeIssue($issueNumber);

                $this->getHelper('gush_style')->success(
                    sprintf(
                        'Closed issue: %s',
                        $adapter->getIssueUrl($issueNumber)
                    )
                );
            }
        }

        return self::COMMAND_SUCCESS;
    }
}
