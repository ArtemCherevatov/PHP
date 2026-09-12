<?php
function formatPoints(int $points): string {
    return "<strong>{$points}</strong> очк.";
}

function rankLabel(int $position): string {
    return "<span class='rank-badge'>#{$position}</span>";
}