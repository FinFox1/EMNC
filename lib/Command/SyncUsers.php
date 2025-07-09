<?php
namespace OCA\ElementMatrix\Command;

use OCA\ElementMatrix\Service\MatrixApiService;
use OCA\ElementMatrix\Service\NextcloudIntegrationService;
use OCP\IUserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncUsers extends Command {
    private IUserManager $userManager;
    private MatrixApiService $matrixApi;
    private NextcloudIntegrationService $integrationService;

    public function __construct(
        IUserManager $userManager,
        MatrixApiService $matrixApi,
        NextcloudIntegrationService $integrationService
    ) {
        parent::__construct();
        $this->userManager = $userManager;
        $this->matrixApi = $matrixApi;
        $this->integrationService = $integrationService;
    }

    protected function configure() {
        $this
            ->setName('elementmatrix:sync-users')
            ->setDescription('Synchronize Nextcloud users with Matrix homeserver');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $output->writeln('Starting user synchronization...');

        $this->userManager->callForAllUsers(function($user) use ($output) {
            $userId = $user->getUID();
            $displayName = $user->getDisplayName();
            $email = $user->getEMailAddress();

            try {
                $matrixUserId = $this->integrationService->getMatrixUserId($userId);
                
                // Create or update Matrix user
                $this->matrixApi->createUser($matrixUserId, $displayName, $email);
                
                $output->writeln("Synced user: $userId -> $matrixUserId");
            } catch (Exception $e) {
                $output->writeln("Failed to sync user $userId: " . $e->getMessage());
            }
        });

        $output->writeln('User synchronization completed.');
        return 0;
    }
}
