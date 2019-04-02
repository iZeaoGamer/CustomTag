<?php

    namespace CustomTag;

    use onebone\economyapi\EconomyAPI;
    use pocketmine\event\Listener;
    use pocketmine\event\player\PlayerJoinEvent;
    use pocketmine\event\server\DataPacketReceiveEvent;
    use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
    use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

    class eventListener implements Listener
    {
        private $main;

        public function __construct(main $main)
        {
            $this->main = $main;
        }

        public function onJoin(PlayerJoinEvent $event)
        {
            $player = $event->getPlayer();
            $name = $player->getName();
            if (!isset($this->main->tag_data->get("player_tag")[$name])) {
                $player_tag = $this->main->tag_data->get("player_tag");
                $player_tag[$name]["tags"] = array();
                $player_tag[$name]["now"] = false;
                $this->main->tag_data->set("player_tag", $player_tag);
            }
            if ($this->main->tag_data->get("player_tag")[$name]["now"] !== false) {
                $tag_name = $this->main->tag_data->get("player_tag")[$name]["now"];
                $player->setNameTag("§b[§r{$tag_name}§r§b] §r{$name}");
                $player->setDisplayName("§b[§r{$tag_name}§r§b] §r{$name}");
            } else {
                $player->setNameTag($name);
                $player->setDisplayName($name);
            }
        }

        public function onDataPacketReceive(DataPacketReceiveEvent $event): void
        {
            $player = $event->getPlayer();
            $name = $player->getName();
            $packet = $event->getPacket();
            if (!$packet instanceof ModalFormResponsePacket) {
                return;
            }
            $formId = $packet->formId;
            $response = json_decode($packet->formData, true);
            switch ($formId) {
                case $this->main->formId[0]:
                    if ($response === null) {
                        return;
                    }
                    switch ($response) {
                        case 0:
                            $xp = $player->getXpLevel();
                            if (count($this->main->tag_data->get("shop_tag")) <= 0) {
                                $player->sendMessage(main::ERROR_TAG . "There is no title which can be purchased now");
                                return;
                            }
                            $form = new ModalFormRequestPacket();
                            $form->formId = $this->main->formId[1];
                            $form_data["type"] = "custom_form";
                            $form_data["title"] = $this->main->getDescription()->getName();
                            $form_data["content"][] = array(
                                "type" => "label",
                                "text" => "Present money: {$xp}",
                            );
                            $form_data["content"][] = array(
                                "type" => "dropdown",
                                "text" => "Title to buy",
                            );
                            foreach ($this->main->tag_data->get("shop_tag") as $shop_tag) {
                                $tag_name = $shop_tag["name"];
                                $tag_price = $shop_tag["price"];
                                $form_data["content"][1]["options"][] = "{$tag_name}§r§e(§l§b{$xp}{$tag_price}§r§e)";
                            }
                            $form->formData = json_encode($form_data);
                            $player->sendDataPacket($form);
                            break;
                        case 1:
                            $player_tag = $this->main->tag_data->get("player_tag");
                            if ($player_tag[$name]["now"] === false) {
                                $now = "なし";
                            } else {
                                $now = $player_tag[$name]["now"];
                            }
                            if (count($this->main->tag_data->get("player_tag")[$name]["tags"]) <= 0) {
                                $player->sendMessage(main::ERROR_TAG . "There is no title currently set");
                                return;
                            }
                            $form = new ModalFormRequestPacket();
                            $form->formId = $this->main->formId[2];
                            $form_data["type"] = "custom_form";
                            $form_data["title"] = $this->main->getDescription()->getName();
                            $form_data["content"][] = array(
                                "type" => "label",
                                "text" => "Current title: {$now}",
                            );
                            $form_data["content"][] = array(
                                "type" => "dropdown",
                                "text" => "Title to set",
                            );
                            foreach ($this->main->tag_data->get("player_tag")[$name]["tags"] as $tag_name) {
                                $form_data["content"][1]["options"][] = "{$tag_name}";
                            }
                            $form->formData = json_encode($form_data);
                            $player->sendDataPacket($form);
                            break;
                        case 2:
                            if ($player->isOp()) {
                                $form = new ModalFormRequestPacket();
                                $form->formId = $this->main->formId[3];
                                $form_data["type"] = "custom_form";
                                $form_data["title"] = $this->main->getDescription()->getName();
                                $form_data["content"][] = array(
                                    "type" => "input",
                                    "text" => "Title to add",
                                );
                                $form_data["content"][] = array(
                                    "type" => "input",
                                    "text" => "price",
                                );
                                $form->formData = json_encode($form_data);
                                $player->sendDataPacket($form);
                            } else {
                                $player->sendMessage(main::ERROR_TAG . "Invalid packet detected");
                            }
                            break;
                        case 3:
                            if ($player->isOp()) {
                          
                                if (count($this->main->tag_data->get("shop_tag")) <= 0) {
                                    $player->sendMessage(main::ERROR_TAG . "There is no title which can be deleted now");
                                    return;
                                }
                                $form = new ModalFormRequestPacket();
                                $form->formId = $this->main->formId[4];
                                $form_data["type"] = "custom_form";
                                $form_data["title"] = $this->main->getDescription()->getName();
                                $form_data["content"][] = array(
                                    "type" => "dropdown",
                                    "text" => "Title to delete",
                                );
                                foreach ($this->main->tag_data->get("shop_tag") as $shop_tag) {
                                    $tag_name = $shop_tag["name"];
                                    $tag_price = $shop_tag["price"];
                                    $form_data["content"][0]["options"][] = "{$tag_name}§r§e(§l§b{$tag_price}§r§e)";
                                }
                                $form->formData = json_encode($form_data);
                                $player->sendDataPacket($form);
                            } else {
                                $player->sendMessage(main::ERROR_TAG . "Invalid packet detected");
                            }
                            break;
                        case 4:
                            if ($player->isOp()) {
                                if (count($this->main->tag_data->get("player_tag")) <= 0) {
                                    $player->sendMessage(main::ERROR_TAG . "There are no players currently set");
                                    return;
                                }
                                $form = new ModalFormRequestPacket();
                                $form->formId = $this->main->formId[5];
                                $form_data["type"] = "custom_form";
                                $form_data["title"] = $this->main->getDescription()->getName();
                                $form_data["content"][] = array(
                                    "type" => "dropdown",
                                    "text" => "Player to set",
                                );
                                $form_data["content"][] = array(
                                    "type" => "input",
                                    "text" => "設定する称号",
                                );
                                foreach ($this->main->tag_data->get("player_tag") as $key => $shop_tag) {
                                    $form_data["content"][0]["options"][] = $key;
                                }
                                $form->formData = json_encode($form_data);
                                $player->sendDataPacket($form);
                            } else {
                                $player->sendMessage(main::ERROR_TAG . "Invalid packet detected");
                            }
                            break;

                    }
                    break;
                case $this->main->formId[1]:
                    if ($response === null) {
                        return;
                    }
                    $xp = $player->getXpLevel();
                    $shop_tag = $this->main->tag_data->get("shop_tag")[$response[1]];
                    $tag_name = $shop_tag["name"];
                    $tag_price = $shop_tag["price"];
                    if ($tag_price > $xp) {
                        $player->sendMessage(main::ERROR_TAG . "There is insufficient xp");
                        return;
                    }
                    $player->setXpLevel($player->getXpLevel() - $tag_price);
                    $player_tag = $this->main->tag_data->get("player_tag");
                    $player_tag[$name]["tags"][] = $tag_name;
                    $player_tag[$name]["now"] = $tag_name;
                    $this->main->tag_data->set("player_tag", $player_tag);
                    $player->setNameTag("§b[§r{$tag_name}§r§b] §r{$name}");
                    $player->setDisplayName("§b[§r{$tag_name}§r§b] §r{$name}");
                    $player->sendMessage(main::SUCCESS_TAG . "I bought a title");
                    break;
                case $this->main->formId[2]:
                    if ($response === null) {
                        return;
                    }
                    $player_tag = $this->main->tag_data->get("player_tag");
                    $tag_name = $player_tag[$name]["tags"][$response[1]];
                    $player_tag[$name]["now"] = $tag_name;
                    $this->main->tag_data->set("player_tag", $player_tag);
                    $player->setNameTag("§b[§r{$tag_name}§r§b] §r{$name}");
                    $player->setDisplayName("§b[§r{$tag_name}§r§b] §r{$name}");
                    $player->sendMessage(main::SUCCESS_TAG . "I set the title");
                    break;
                case $this->main->formId[3]:
                    if ($response === null) {
                        return;
                    }
                    if ($player->isOp()) {
                        if ($response[0] === "") {
                            $player->sendMessage(main::ERROR_TAG . "Title has not been entered");
                            return;
                        }
                        if (!is_numeric($response[1])) {
                            $player->sendMessage(main::ERROR_TAG . "The price is not a number");
                            return;
                        }
                        $shop_tag = $this->main->tag_data->get("shop_tag");
                        $shop_tag[] = array(
                            "name" => $response[0],
                            "price" => $response[1],
                        );
                        $this->main->tag_data->set("shop_tag", $shop_tag);
                        $player->sendMessage(main::SUCCESS_TAG . "I registered the title");
                    } else {
                        $player->sendMessage(main::ERROR_TAG . "Invalid packet detected");
                    }
                    break;
                case $this->main->formId[4]:
                    if ($response === null) {
                        return;
                    }
                    if ($player->isOp()) {
                        $shop_tag = $this->main->tag_data->get("shop_tag");
                        //print_r($shop_tag);
                        unset($shop_tag[$response[0]]);
                        //print_r($shop_tag);
                        $shop_tag = array_merge($shop_tag);
                        //print_r($shop_tag);
                        $this->main->tag_data->set("shop_tag", $shop_tag);
                        $player->sendMessage(main::SUCCESS_TAG . "I deleted the title");
                    } else {
                        $player->sendMessage(main::ERROR_TAG . "Invalid packet detected");
                    }
                    break;
                case $this->main->formId[5]:
                    if ($response === null) {
                        return;
                    }
                    if ($player->isOp()) {
                        if ($response[1] === "") {
                            $player->sendMessage(main::ERROR_TAG . "Title has not been entered");
                            return;
                        }
                        $player_tag = $this->main->tag_data->get("player_tag");
                        $names = array_keys($player_tag);
                        $player_tag[$names[$response[0]]]["now"] = $response[1];
                        $this->main->tag_data->set("player_tag", $player_tag);
                        $player->setNameTag("§b[§r{$response[1]}§r§b] §r{$name}");
                        $player->setDisplayName("§b[§r{$response[1]}§r§b] §r{$name}");
                        $player->sendMessage(main::SUCCESS_TAG . "I set the title");
                    } else {
                        $player->sendMessage(main::ERROR_TAG . "Invalid packet detected");
                    }
                    break;
            }
        }
    }
