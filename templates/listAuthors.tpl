{include file="frontend/components/header.tpl" pageTitle="plugins.authorListing.heading"}

<main class="page page_authors">
	<div class="container-fluid container-page container-narrow">
		{include file="frontend/components/headings.tpl" currentTitleKey="plugins.authorListing.heading"}

		{$currentContext->getLocalizedSetting('about')}
	</div>
</main><!-- .page -->

{include file="frontend/components/footer.tpl"}
