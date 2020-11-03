<?php

/**
 * Cadastro de RPA
 *  
 * By Alat
 */

# Verifica se tem cpf na session indicando
# que veio de um cadastro de rpa
$sessionCpfPrestador = get_session('sessionCpfPrestador');

if(!empty($sessionCpfPrestador)){
    set_session('sessionidPrestador', $id);
    set_session('sessionCpfPrestador');
    $this->linkListar = 'cadastroRpa.php?fase=editar2';
}
