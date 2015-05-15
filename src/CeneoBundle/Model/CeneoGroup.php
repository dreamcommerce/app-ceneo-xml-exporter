<?php


namespace CeneoBundle\Model;


class CeneoGroup
{

    public static $groups = array(
        'other' => array('Inne', array(
            'Producent', 'Kod_producenta', 'EAN'
        )),
        'grocery' => array('Delikatesy', array(
            'Producent', 'EAN', 'Ilosc'
        )),
        'rims' => array('Felgi i kołpaki', array(
            'Producent', 'Kod_producenta', 'EAN', 'Rozmiar', 'Rozstaw_srub', 'Odsadzenie'
        )),
        'movies' => array('Filmy', array(
            'Rezyser', 'EAN', 'Nosnik', 'Wytwornia', 'Obsada', 'Tytul_oryginalny'
        )),
        'games' => array('Gry PC / Gry na konsole', array(
            'Producent', 'Kod_producenta', 'EAN', 'Platforma', 'Gatunek'
        )),
        'books' => array('Książki', array(
            'Autor', 'ISBN', 'Ilosc_stron', 'Wydawnictwo', 'Rok_wydania', 'Oprawa', 'Format', 'Spis_tresci', 'Fragment'
        )),
        'medicines' => array('Leki, suplementy', array(
            'Producent', 'BLOZ_12', 'BLOZ_7', 'Ilosc'
        )),
        'clothes' => array('Odzież, obuwie, dodatki', array(
            'Producent', 'Model', 'EAN', 'Kolor', 'Rozmiar', 'Kod_produktu', 'Sezon', 'Fason', 'ProductSetId'
        )),
        'tires' => array('Opony', array(
            'Producent', 'SAP', 'EAN', 'Model', 'Szerokosc_opony', 'Profil', 'Srednica_kola', 'Indeks_predkosc', 'Indeks_nosnosc', 'Sezon'
        )),
        'perfumes' => array('Perfumy', array(
            'Producent', 'Kod_producenta', 'EAN', 'Linia', 'Rodzaj', 'Pojemnosc'
        )),
        'music' => array('Płyty muzyczne', array(
            'Wykonawca', 'EAN', 'Nosnik', 'Wytwornia', 'Gatunek'
        ))
    );

}