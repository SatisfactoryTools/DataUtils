<?php declare(strict_types = 1);

namespace SFTools\Data\Parser\Handlers;

use SFTools\Data\Parser\ClassData;
use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Parts\GamePhase;
use SFTools\Data\Schema\Parts\ItemAmount;
use SFTools\Data\Schema\Parts\ScannableObject;
use SFTools\Data\Schema\Parts\SchematicType;
use SFTools\Data\Schema\Schematic;
use SFTools\Data\Unpacker;

class SchematicHandler extends ClassNameFilteredHandler
{

	public function handle(DocsSchema $schema, ClassData $data): void
	{
		$className = $data->getString('ClassName');

		$displayName = $data->getString('mDisplayName');

		$schematic = new Schematic;
		$schematic->className = $className;
		$schematic->icon = $data->getBrushIcon('mSchematicIcon');
		if (!$schematic->icon) {
			$smallIcon = $data->getString('mSmallSchematicIcon');
			$schematic->icon = $smallIcon !== '' && $smallIcon !== 'None' ? $smallIcon : null;
		}
		$schematic->name = $displayName;
		$schematic->description = $data->getString('mDescription');
		$schematic->tier = $data->getInt('mTechTier');
		$schematic->cost = $data->getItemAmountArray('mCost');
		$schematic->time = $data->getFloat('mTimeToComplete');
		$schematic->dependenciesBlockAccess = $data->getBool('mDependenciesBlocksSchematicAccess');
		$schematic->dependenciesHide = $data->getBool('mHiddenUntilDependenciesMet');
		$schematic->events = $data->getEvents('mRelevantEvents');
		$schematic->type = SchematicType::fromString($data->getString('mType'));

		/** @var array{'Class': string, 'mScannableObjects': string, 'mResourcesToAddToScanner': string, 'mRecipes': string, 'mNumInventorySlotsToUnlock': string, 'mNumArmEquipmentSlotsToUnlock': string, 'mSchematics': string, 'mItemsToGive': string, 'mEmotes': string, 'mTapeUnlocks': string} $unlock */
		foreach ($data->getRawArray('mUnlocks') as $unlock) {
			switch ($unlock['Class']) {
				case 'BP_UnlockScannableObject_C':
					/** @var array{'ItemDescriptor': string, 'ActorsAllowedToScan': array<string>} $object */
					foreach ((array) Unpacker::unpack($unlock['mScannableObjects']) as $object) {
						$schematic->unlock->scannableObjects[] = new ScannableObject(
							ClassData::parseBlueprintClass($object['ItemDescriptor']),
							$object['ActorsAllowedToScan'], // TODO parse into items
						);
					}
					break;

				case 'BP_UnlockScannableResource_C':
					/** @var string $resource */
					foreach ((array) Unpacker::unpack($unlock['mResourcesToAddToScanner']) as $resource) {
						$schematic->unlock->scannableResources[] = ClassData::parseBlueprintClass($resource);
					}
					break;

				case 'BP_UnlockRecipe_C':
					/** @var string $recipe */
					foreach ((array) Unpacker::unpack($unlock['mRecipes']) as $recipe) {
						$schematic->unlock->recipes[] = ClassData::parseBlueprintClass($recipe);
					}
					break;

				case 'BP_UnlockInventorySlot_C':
					$schematic->unlock->inventorySlots = (int) $unlock['mNumInventorySlotsToUnlock'];
					break;

				case 'BP_UnlockArmEquipmentSlot_C':
					$schematic->unlock->equipmentSlots = (int) $unlock['mNumArmEquipmentSlotsToUnlock'];
					break;

				case 'BP_UnlockSchematic_C':
					/** @var string $s */
					foreach ((array) Unpacker::unpack($unlock['mSchematics']) as $s) {
						$schematic->unlock->schematics[] = ClassData::parseBlueprintClass($s);
					}
					break;

				case 'BP_UnlockGiveItem_C':
					/** @var array{'ItemClass': string, 'Amount': string} $item */
					foreach ((array) Unpacker::unpack($unlock['mItemsToGive']) as $item) {
						$schematic->unlock->items[] = new ItemAmount(
							ClassData::parseBlueprintClass($item['ItemClass']),
							(float) $item['Amount'],
						);
					}
					break;

				case 'BP_UnlockEmote_C':
					/** @var string $emote */
					foreach ((array) Unpacker::unpack($unlock['mEmotes']) as $emote) {
						$schematic->unlock->emotes[] = ClassData::parseBlueprintClass($emote);
					}
					break;

				case 'FGUnlockTape':
					/** @var string $tape */
					foreach ((array) Unpacker::unpack($unlock['mTapeUnlocks']) as $tape) {
						$schematic->unlock->tapes[] = ClassData::parseBlueprintClass($tape);
					}
					break;
			}
		}

		/** @var array{'Class': string, 'mSchematics': string, 'mRequireAllSchematicsToBePurchased': string, 'mGamePhase': string} $dependency */
		foreach ($data->getRawArray('mSchematicDependencies') as $dependency) {
			switch ($dependency['Class']) {
				case 'BP_SchematicPurchasedDependency_C':
					/** @var string $s */
					foreach ((array) Unpacker::unpack($dependency['mSchematics']) as $s) {
						$schematic->dependency->schematics[] = ClassData::parseBlueprintClass($s);
					}
					$schematic->dependency->requireAll = $dependency['mRequireAllSchematicsToBePurchased'] === 'True';
					break;

				case 'BP_GamePhaseReachedDependency_C':
					$schematic->dependency->gamePhase = GamePhase::tryFrom(lcfirst($dependency['mGamePhase']));
					break;
			}
		}

		$schema->schematics[$schematic->className] = $schematic;
	}

	protected function getClassNames(): array
	{
		return ['/Script/FactoryGame.FGSchematic'];
	}

}
