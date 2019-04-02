<?php

    namespace CustomTag;


    use pocketmine\command\Command;
    use pocketmine\command\CommandSender;
    use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
    use pocketmine\Player;

    class tagCommand extends Command
    {

        private $main;

        public function __construct(main $main)
        {
            $this->main = $main;
            parent::__construct("tag", "title", "/tag");
            $this->setPermission("customtag.command.tag");
            $this->setDescription("Tag command");
            $this->setUsage("/tag");
        }

        public function execute(CommandSender $sender, string $commandLabel, array $args): bool
        {
            if (!$sender instanceof Player) {
                $sender->sendMessage(main::ERROR_TAG . "This command can only be executed by the player");
                return true;
            } else {
                $player = $sender->getPlayer();
                $name = $player->getName();
            }
            //print_r($this->main->tag_data->getAll());
            $form = new ModalFormRequestPacket();
            $form->formId = $this->main->formId[0];
            $form_data["type"] = "form";
            $form_data["title"] = $this->main->getDescription()->getName();
            if (!$player->isOp()) {
                $form_data["content"] = "Select the action you want to perform\n§l§a[General mode]";
                $form_data["buttons"][] = array(
                    "text" => "§l§aPurchase a title",
                );
                $form_data["buttons"][] = array(
                    "text" => "§l§aSet the title",
                );
            } else {
                $form_data["content"] = "Select the action you want to perform \ n§l§c[Administrator mode]";
                $form_data["buttons"][] = array(
                    "text" => "§l§aPurchase a title",
                );
                $form_data["buttons"][] = array(
                    "text" => "§l§aSet the title",
                );
                $form_data["buttons"][] = array(
                    "text" => "§l§6[OP]§aAdd a title",
                );
                $form_data["buttons"][] = array(
                    "text" => "§l§6[OP]§aDelete the title",
                );
                $form_data["buttons"][] = array(
                    "text" => "§l§6[OP]§aForced title setting",
                );
            }
            $form->formData = json_encode($form_data);
            $player->sendDataPacket($form);
            return true;
        }

    }
