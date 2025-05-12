{include file="frontend/components/header.tpl" pageTitle="plugins.authorListing.page"}

<main class="page page_search">
	<section class="container-fluid container-page">

        <h1>Articles with author {$author|escape}</h1>

        <div class="search_results">
            {foreach from=$results item=$result}
                {include file="frontend/objects/article_summary.tpl" headingLevel="2" article=$result.publishedSubmission journal=$result.journal showDatePublished=true hideGalleys=true}
            {/foreach}
        </div>

	</section>
</main><!-- .page -->

{include file="frontend/components/footer.tpl"}
