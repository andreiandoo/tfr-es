<?php
global $wpdb;

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

$classes = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT c.*, s.name AS school_name, city.name AS city_name, county.name AS county_name
         FROM {$wpdb->prefix}edu_classes c
         JOIN {$wpdb->prefix}edu_schools s ON c.school_id = s.id
         JOIN {$wpdb->prefix}edu_cities city ON s.city_id = city.id
         JOIN {$wpdb->prefix}edu_counties county ON city.county_id = county.id
         WHERE c.teacher_id = %d
         ORDER BY c.name",
        $user_id
    )
);
?>

<div class="w-full transition-content px-(--margin-x) pb-8">
    <div class="transition-content flex items-center justify-between gap-4 px-(--margin-x) pt-4 mb-6">
        <div class="flex items-center min-w-0 gap-4">
            <h2 class="text-xl font-medium tracking-wide text-gray-800 truncate dark:text-dark-50 lg:text-2xl">Clasele mele</h2>
            <div class="self-stretch hidden py-1 sm:flex">
                <div class="w-px h-full bg-gray-300 dark:bg-dark-600"></div>
            </div>
        </div>

        <div class="flex space-x-2 ">
            <button id="toggleAddForm" class="flex items-center h-8 px-3 space-x-2 text-xs text-gray-900 border border-gray-300 rounded-md gap-x-2 btn-base btn hover:bg-gray-300/20 focus:bg-gray-300/20 active:bg-gray-300/25">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
                <path d="M5.25 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM2.25 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM18.75 7.5a.75.75 0 0 0-1.5 0v2.25H15a.75.75 0 0 0 0 1.5h2.25v2.25a.75.75 0 0 0 1.5 0v-2.25H21a.75.75 0 0 0 0-1.5h-2.25V7.5Z" />
                </svg>
                Adaugă o clasă nouă
            </button>
        </div>
    </div>



    
<?php

if (empty($classes)) {
    echo '<p class="text-gray-600">Nu ai clase asociate momentan.</p>';
    return;
}
?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
<?php
foreach ($classes as $class) {
    $class_link = esc_url(home_url('/panou/clase/clasa-' . $class->id));

    // Obține numărul de elevi
    $student_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}edu_students WHERE class_id = %d",
            $class->id
        )
    );

    // Placeholder pentru scoruri
    $scor_sel = '—';
    $scor_lit = '—';

    echo '
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 hover:shadow-md transition">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold text-blue-600 hover:underline">
                    <a href="' . $class_link . '">' . esc_html($class->name) . '</a>
                </h3>
                <p class="text-sm text-gray-700 mt-1">' . esc_html($class->school_name) . '</p>
                <p class="text-sm text-gray-500">' . esc_html($class->city_name . ', ' . $class->county_name) . '</p>
            </div>
            <span class="ml-2 inline-block px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded">
                ' . esc_html($class->level) . '
            </span>
        </div>

        <div class="mt-4 text-sm text-gray-800">
            <p><strong>Număr elevi:</strong> ' . intval($student_count) . '</p>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
            <div class="bg-gray-50 p-3 rounded border">
                <p class="font-semibold text-gray-600">Scor SEL</p>
                <p class="text-xl text-blue-800">' . $scor_sel . '</p>
            </div>
            <div class="bg-gray-50 p-3 rounded border">
                <p class="font-semibold text-gray-600">Scor Literație</p>
                <p class="text-xl text-green-800">' . $scor_lit . '</p>
            </div>
        </div>

        <div class="mt-4">
            <a href="' . $class_link . '" class="inline-block text-sm text-blue-600 hover:underline font-medium">
                Vezi detalii &rarr;
            </a>
        </div>
    </div>';
}
?>
</div>
</div>