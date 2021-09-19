
<?php if (! isset($_POST['IgnoreTitle'])): ?>

    </main><!-- end main#content -->

    <footer id="footer" class="layoutSection">
        <div class="footer_section">
        Rialto &copy; <?php echo date('Y'); ?>
        <a href="http://www.gumstix.com">Gumstix, Inc.</a>
        <?php if ( APPLICATION_ENV != 'production' ): ?>
            <span class="appEnv">
                <?php echo strtoupper(APPLICATION_ENV); ?>
            </span>
        <?php endif; ?>
        </div>
        <div class="footer_section">
        <button target="_blank"
                onclick="window.open('http://www.featurestix.com/bug_report_page.php?category_id=1&description=<?php echo urlencode(sprintf('On page %s:', $_SERVER['REQUEST_URI'])); ?>');"
            title="Report issue">
            <img src="/icons/oxygen/16x16/actions/tools-report-bug.png"
                 alt="Report issue" />
        </button>
        <button type="button"
               onclick="window.location = window.location;"
               title="Refresh page">
            <img src="/icons/oxygen/16x16/actions/view-refresh.png"
                 alt="Refresh page" />
        </button>
        </div>
    </footer>
<?php endif; ?>

</body>
</html>
