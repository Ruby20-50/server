<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OC\Core\Command\Search;

use NCU\Search\IAccountScopedSearchProviderRegistry;
use OCP\Console\Attribute\AsCommand;
use OCP\Console\ExitCode;
use OCP\Console\IOutput;

#[AsCommand(
	name: 'search:providers',
	description: 'List the registered account-scoped search providers and the fields each one can be searched on',
	help: 'This command is experimental: it queries NCU\Search\IAccountScopedSearchProvider, which is itself experimental and may still change or be removed.',
	supportsOutputFormat: true,
)]
class ProvidersCommand {
	public function __construct(
		private readonly IAccountScopedSearchProviderRegistry $registry,
	) {
	}

	public function __invoke(IOutput $output): ExitCode {
		$providers = $this->registry->getProviders();
		if ($providers === []) {
			$output->writeln('No account-scoped search providers are registered.');

			return ExitCode::Success;
		}

		$tree = [];
		foreach ($providers as $id => $provider) {
			$fields = [];
			foreach ($provider->getSearchCriterion() as $field) {
				$fields[] = $field['id'] . ' — ' . $field['title']
					. ' [' . $field['type'] . ', default=' . $this->formatValue($field['default'] ?? '') . ']';
			}
			$tree[$id . ' (' . $provider->getName() . ')'] = $fields;
		}

		$output->writeTree($tree, 'Account-scoped search providers');

		return ExitCode::Success;
	}

	private function formatValue(mixed $value): string {
		return is_scalar($value) ? (string)$value : (json_encode($value) ?: '');
	}
}
