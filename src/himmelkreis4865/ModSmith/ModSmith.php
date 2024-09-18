<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith;

use customiesdevs\customies\item\CustomiesItemFactory;
use GdImage;
use himmelkreis4865\ModSmith\inventory\CustomInventoryRegistry;
use himmelkreis4865\ModSmith\item\ItemAssetRegistry;
use himmelkreis4865\ModSmith\item\ProgressBarPlaceholder;
use himmelkreis4865\ModSmith\tasks\ResourcePackCreateTask;
use himmelkreis4865\ModSmith\utils\FileRegistry;
use himmelkreis4865\ModSmith\utils\LanguageRegistry;
use himmelkreis4865\ModSmith\utils\Texture;
use himmelkreis4865\ModSmith\utils\UIDefs;
use himmelkreis4865\ModSmith\utils\Utils;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\math\Facing;
use pocketmine\plugin\PluginBase;
use pocketmine\resourcepacks\ZippedResourcePack;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use RuntimeException;
use function basename;
use function file_exists;
use function imagecreatefrompng;
use function json_encode;
use function str_replace;

final class ModSmith extends PluginBase {
	use SingletonTrait;

	/** @var Texture[]|null $defaultProgressBarArrowTextures */
	private ?array $defaultProgressBarArrowTextures = null;

	private string $resourcePackPath;

	/**
	 * @param Texture[] $textures
	 * @return Item[]
	 */
	public function registerProgressbarItems(array $textures, string $prefix = ""): array {
		$items = [];
		foreach ($textures as $texture) {
			$name = $prefix . str_replace(".png", "", basename($texture->texture));
			$identifier = "modsmith:" . $name;

			if (StringToItemParser::getInstance()->parse($identifier) !== null) {
				$items[] = CustomiesItemFactory::getInstance()->get($identifier);
				continue;
			}

			ItemAssetRegistry::getInstance()->addTexture($texture);

			CustomiesItemFactory::getInstance()->registerItem(ProgressBarPlaceholder::class, $identifier, $name);
			$items[] = CustomiesItemFactory::getInstance()->get($identifier);
		}
		return $items;
	}

	/**
	 * @param string $texturePath Describes the base path to the image, for each iteration represented as "i", _i.png will be added to the path
	 * @return Texture[]
	 */
	public function generateProgressBarTextures(GdImage $empty, GdImage $full, string $texturePath, int $states, int $facingFrom): array {
		$images = Utils::generateProgressBarImages($empty, $full, $states, $facingFrom);
		$textures = [];

		foreach ($images as $i => $image) {
			$textures[] = Texture::fromImage($texturePath . "_" . $i . ".png", $image);
		}
		return $textures;
	}

	protected function onLoad(): void {
		self::$instance = $this;
	}

	protected function onEnable(): void {
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		$this->resourcePackPath = Server::getInstance()->getResourcePackManager()->getPath() . "ModSmith.zip";

		$this->getScheduler()->scheduleTask(new ClosureTask(fn() => $this->loadResourcePack()));
	}

	private function loadResourcePack(): void {
		if ($this->getConfig()->get("mode") === "production" && file_exists($this->resourcePackPath)) {
			$this->registerResourcePack();
			$this->getLogger()->info("Loaded existing resource pack without creating a new one");
		} else {
			$this->getLogger()->info("Creating resource pack...");
			$files = [];
			foreach (CustomInventoryRegistry::getInstance()->getFileContents() as $name => $content) {
				$files[$name] = $content;
			}
			$files["ui/_ui_defs.json"] = UIDefs::getInstance()->encode();

			LanguageRegistry::getInstance()->save();
			foreach (FileRegistry::getInstance()->getFiles() as $path => $content) {
				$files[$path] = $content;
			}

			$this->getServer()->getAsyncPool()->submitTask(new ResourcePackCreateTask(
				$files,
				$this->resourcePackPath,
				$this->getDataFolder() . "packdata.cache"
			));
		}
	}

	public function registerResourcePack(): void {
		$manager = Server::getInstance()->getResourcePackManager();
		$manager->setResourceStack([...$manager->getResourceStack(), new ZippedResourcePack($this->resourcePackPath)]);
	}

	/**
	 * @return Item[]
	 */
	public function registerDefaultProgressBarArrow(int $states = 16): array {
		return $this->registerProgressbarItems($this->getDefaultProgressBarArrowTextures($states));
	}

	/**
	 * @return Texture[]
	 */
	public function getDefaultProgressBarArrowTextures(int $states = 16): array {
		$arrowEmpty = imagecreatefrompng($this->getResourceFolder() . "progress_bar/arrow_empty.png");
		$arrowFull = imagecreatefrompng($this->getResourceFolder() . "progress_bar/arrow_full.png");
		if (!$arrowEmpty || !$arrowFull) {
			throw new RuntimeException("Failed to load default arrow textures.");
		}
		return $this->defaultProgressBarArrowTextures ??= $this->generateProgressBarTextures(
			$arrowEmpty,
			$arrowFull,
			"textures/items/progress_bars/progress_arrow",
			$states,
			Facing::WEST
		);
	}
}