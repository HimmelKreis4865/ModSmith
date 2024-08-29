<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\utils;

use InvalidArgumentException;
use pocketmine\utils\SingletonTrait;
use function count;

final class LanguageRegistry {
	use SingletonTrait;

	/** @var array<string, array<string, string>> $languageKeys */
	private array $languageKeys = [];

	public function translate(string $key, Language $language, string $translation): void {
		$this->languageKeys[$language->name][$key] = $translation;
	}

	/**
	 * @param Language[] $languages
	 * @param string[]   $translations
	 */
	public function translateMultipleLanguages(string $key, array $languages, array $translations): void {
		if (count($languages) !== count($translations)) {
			throw new InvalidArgumentException("Cannot add an amount of translations to a different amount of languages");
		}
		foreach ($languages as $i => $language) {
			if ($language instanceof Language) {
				$this->translate($key, $language, $translations[$i]);
			}
		}
	}

	/**
	 * @param array<string, string> $translations the array key is the translation key here
	 */
	public function translateMultipleKeys(Language $language, array $translations): void {
		foreach ($translations as $key => $translation) {
			$this->translate($key, $language, $translation);
		}
	}

	public function translateBlock(string $blockIdentifier, Language $language, string $translation): void {
		$this->translate($this->blockIdentifierToKey($blockIdentifier), $language, $translation);
	}

	/**
	 * @param array<string, string> $translations the array key equals the block's identifier
	 */
	public function translateBlocks(Language $language, array $translations): void {
		$t = [];
		foreach ($translations as $blockIdentifier => $translation) {
			$t[$this->blockIdentifierToKey($blockIdentifier)] = $translation;
		}
		$this->translateMultipleKeys($language, $t);
	}

	/**
	 * @param Language[] $languages
	 * @param string[]   $translations
	 */
	public function multiTranslateBlock(string $blockIdentifier, array $languages, array $translations): void {
		$this->translateMultipleLanguages($this->blockIdentifierToKey($blockIdentifier), $languages, $translations);
	}

	private function blockIdentifierToKey(string $blockIdentifier): string {
		return "tile.$blockIdentifier.name";
	}

	public function translateItem(string $itemIdentifier, Language $language, string $translation): void {
		$this->translate($this->itemIdentifierToKey($itemIdentifier), $language, $translation);
	}

	/**
	 * @param array<string, string> $translations the array key equals the block's identifier
	 */
	public function translateItems(Language $language, array $translations): void {
		$t = [];
		foreach ($translations as $itemIdentifier => $translation) {
			$t[$this->itemIdentifierToKey($itemIdentifier)] = $translation;
		}
		$this->translateMultipleKeys($language, $t);
	}

	/**
	 * @param Language[] $languages
	 * @param string[]   $translations
	 */
	public function multiTranslateItem(string $itemIdentifier, array $languages, array $translations): void {
		$this->translateMultipleLanguages($this->itemIdentifierToKey($itemIdentifier), $languages, $translations);
	}

	private function itemIdentifierToKey(string $itemIdentifier): string {
		return $itemIdentifier; // todo: can this be "item.$itemIdentifier.name"?
	}

	public function save(): void {
		if (empty($this->languageKeys)) {
			return;
		}
		foreach ($this->languageKeys as $language => $translations) {
			$file = "texts/$language.lang";
			$content = "";
			foreach ($translations as $key => $translation) {
				$content .= $key . "=" . $translation . "\n";
			}
			FileRegistry::getInstance()->addFile($file, $content);
		}
	}
}