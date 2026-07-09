<?php declare(strict_types = 1);

namespace SFTools\Data\Transformers;

use SFTools\Data\Schema\DocsSchema;
use SFTools\Data\Schema\Item;
use SFTools\Data\Schema\Parts\StackSize;
use Symfony\Component\Console\Output\OutputInterface;

class MissingEntryCompletionTransformer implements Transformer
{

	private const CouponClass = 'Desc_ResourceSinkCoupon_C';

	public function transform(DocsSchema $schema, OutputInterface $output): void
	{
		if (!isset($schema->items[self::CouponClass])) {
			$output->writeln('Adding missing ' . self::CouponClass . ' entry', OutputInterface::VERBOSITY_VERY_VERBOSE);

			$item = new Item;
			$item->className = self::CouponClass;
			$item->name = 'FICSIT Coupon';
			$item->description = 'A special FICSIT bonus program Coupon, obtained through the AWESOME Sink. Can be redeemed in the AWESOME Shop for bonus milestones and rewards.';
			$item->stackSize = StackSize::Huge;
			$item->smallIcon = 'Texture2D /Game/FactoryGame/Resource/Parts/ResourceSinkCoupon/UI/IconDesc_Ficsit_Coupon_256.IconDesc_Ficsit_Coupon_256';
			$item->bigIcon = 'Texture2D /Game/FactoryGame/Resource/Parts/ResourceSinkCoupon/UI/IconDesc_Ficsit_Coupon_256.IconDesc_Ficsit_Coupon_256';
			$item->icon = 'Texture2D /Game/FactoryGame/Resource/Parts/ResourceSinkCoupon/UI/IconDesc_Ficsit_Coupon_256.IconDesc_Ficsit_Coupon_256';

			$schema->items[self::CouponClass] = $item;
		}
	}

}
