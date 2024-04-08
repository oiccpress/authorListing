{include file="frontend/components/header.tpl" pageTitle="plugins.authorListing.heading"}

<main class="page page_authors">
	<div class="container-fluid container-page container-narrow">
		{include file="frontend/components/headings.tpl" currentTitleKey="plugins.authorListing.heading"}

		<div class="row">
			<div>
			{capture assign="lastLetter"}0{/capture}
			{foreach from=$authors item=author}
				{if $lastLetter != $author->letter}
					</div>
					<div class="col-md-3 my-3">
						<h2>{$author->letter}</h2>
				{/if}
				<a class="d-block"
						title="{$author->affiliation|escape:'htmlall'}"
						href="{url page="search"}?authors={$author->givenName|escape:'url'} {$author->familyName|escape:'url'}">
					{$author->familyName}, {$author->givenName}
				</a>
				{capture assign="lastLetter"}{$author->letter}{/capture}
			{/foreach}
			</div>
		</div>
	</div>
</main><!-- .page -->

{include file="frontend/components/footer.tpl"}
