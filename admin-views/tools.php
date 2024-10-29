<?php
    $default_tools_sections = array(
        'bulk-delete-entries' => array(
            'name' => 'Bulk Delete Entries',
            'template' => 'tools-sections/bulk-delete-entries.php'
        ),
        'export-entries' => array(
            'name' => 'Export Entries',
            'template' => 'tools-sections/export-entries.php'
        )
    );

    $tools_sections = apply_filters('alchemyst_forms:tools-sections', $default_tools_sections);
?>

<div class="wrap">
    <?=Alchemyst_Forms_Utils::render_logo()?>

    <h1>Alchemyst Forms Tools</h1>

    <div id="alchemyst-forms-tabs">
        <?php $i = 0; foreach ($tools_sections as $section_tag => $section) : ?>
            <a class="nav-tab<?=($i == 0 ? ' nav-tab-active' : null)?>" href="#<?=$section_tag?>"><?=$section['name']?></a>
        <?php $i++; endforeach; ?>
        <div style="clear: both;"></div>
    </div>

    <?php $i = 0; foreach ($tools_sections as $section_tag => $section) : ?>
        <div class="alchemyst-forms-tab-section<?=($i == 0 ? ' alchemyst-forms-tab-section-active' : null)?>" data-alchemyst-forms-tab-section="#<?=$section_tag?>">
            <?php include $section['template']; ?>
        </div>
    <?php $i++; endforeach; ?>
</div>
