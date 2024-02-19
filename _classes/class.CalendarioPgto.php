<?php

class CalendarioPgto {

    /**
     * Exibe o calendário de pgto
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */

    ###########################################################

    /**
     * Método exibeCalendario
     * Exibe o calendário do ano informado
     * 
     * @param    integer $ano O ano do calendário. se nullo exibe o do ano vigente
     */
    public function exibeCalendario($ano = null) {

        $array = [
            ["Janeiro", "05/02"],
            ["Fevereiro", "05/03"],
            ["Março", "03/04"],
            ["Abril", "06/05"],
            ["Maio", "05/06"],
            ["13º Salário (1ª Parcela)", "28/06"],
            ["Junho", "03/07"],
            ["Julho", "05/08"],
            ["Agosto", "04/09"],
            ["Setembro", "03/10"],
            ["Outubro", "05/11"],
            ["Novembro", "04/12"],
            ["13º Salário (2ª Parcela)", "20/12"],
            ["Dezembro", "06/01/2025"]
        ];

        # Exemplo mais complexo
        $tabela = new Tabela();
        $tabela->set_titulo("Calendário de Pagamento");
        $tabela->set_subtitulo("2024");
        $tabela->set_conteudo($array);
        $tabela->set_label(["Mês de Competência", "Data do Pagamento"]);
        $tabela->set_align(["left", "center"]);
        $tabela->show();
    }

    ###########################################################
}
