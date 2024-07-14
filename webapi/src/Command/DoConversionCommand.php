<?php

namespace App\Command;

use App\DataTransferObject\ConversionData;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[AsCommand(
    name: 'doc2md',
    description: 'Converts Word documents to Markdown',
)]
class DoConversionCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('source', InputArgument::OPTIONAL, 'Argument description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $source = $input->getArgument('source');

        if (!$source) {
            $io->note(('You mush indicate the path to the Word document to be converted.'));
        }

        $contents = file_get_contents($source);
        $response = static::performRequest('POST', 'http://127.0.0.1:8000/conversion', new ConversionData(base64_encode($contents), null));
        if ($response->getStatusCode() !== 200) {
            $io->error(sprintf('Something went wrong! %s', $response->getContent(false)));
            return Command::FAILURE;
        }
        $target = str_replace('.docx', '.md', $source);
        file_put_contents($target, $response->getContent());

        $io->success(sprintf('Document successfully converted: %s', $target));

        return Command::SUCCESS;
    }

    protected static function performRequest(string $method, string $routeUrl, ?object $body, bool $isAuthorized = true): ResponseInterface
    {
        //$httpClient = static::createClient();
        $httpClient = HttpClient::create(['timeout' => 60 /* seconds */ * 10]);
        $headerList = [];
        if ($isAuthorized) {
            $headerList['Authorization'] = 'Bearer ' . $_ENV['BEARER'];
        }
        if ($method === 'POST' || $method === 'PUT') {
            $headerList['Content-Type'] = 'application/json';
            $response = $httpClient->request(
                $method,
                $routeUrl,
                [
                    'body' => json_encode($body, JSON_THROW_ON_ERROR),
                    'headers' => $headerList
                ]
            );
        } else {
            $response = $httpClient->request(
                $method,
                $routeUrl,
                ['headers' => $headerList]
            );
        }
        return $response;
    }
}

