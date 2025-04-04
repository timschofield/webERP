
INSERT INTO `accountsection` VALUES (10,'الأصول');
INSERT INTO `accountsection` VALUES (20,'التزامات');
INSERT INTO `accountsection` VALUES (30,'دخل');
INSERT INTO `accountsection` VALUES (40,'التكاليف');

INSERT INTO `accountgroups` VALUES ('الاصول الثابتة',10,0,3000,'');
INSERT INTO `accountgroups` VALUES ('راد استشارات',30,1,8000,'');
INSERT INTO `accountgroups` VALUES ('مصروفات ادارية و عمومية',40,1,12000,'');
INSERT INTO `accountgroups` VALUES ('مصروفات الاجور',40,1,11000,'');
INSERT INTO `accountgroups` VALUES ('الاصول المتداولة',10,0,1000,'');
INSERT INTO `accountgroups` VALUES ('التزامات قصيرة الاجل',20,0,4000,'');
INSERT INTO `accountgroups` VALUES ('التمات طويلة الاجل',20,0,5000,'');
INSERT INTO `accountgroups` VALUES ('المخزون كاصل',10,0,2000,'');
INSERT INTO `accountgroups` VALUES ('ايراد المبيعات',30,1,7000,'');
INSERT INTO `accountgroups` VALUES ('ايردات اخرى',30,1,9000,'');
INSERT INTO `accountgroups` VALUES ('تكلفة البضاعة المباعة',40,1,10000,'');
INSERT INTO `accountgroups` VALUES ('حقوق الملكية',20,0,6000,'');

INSERT INTO `chartmaster` VALUES ('1060','نقدية بالبنك','الاصول المتداولة',-1);
INSERT INTO `chartmaster` VALUES ('1065','نقديةبالصندوق','الاصول المتداولة',-1);
INSERT INTO `chartmaster` VALUES ('1200','عملاء','الاصول المتداولة',-1);
INSERT INTO `chartmaster` VALUES ('1205','مخصص ديون معدومة','الاصول المتداولة',-1);
INSERT INTO `chartmaster` VALUES ('1520','مخزون - قطع غيار كمبيوتر','المخزون كاصل',-1);
INSERT INTO `chartmaster` VALUES ('1530','مخزون - برامج','المخزون كاصل',-1);
INSERT INTO `chartmaster` VALUES ('1540','مخزون - قطع اخرى','المخزون كاصل',-1);
INSERT INTO `chartmaster` VALUES ('1820','اثاث مكتبى و معدات','الاصول الثابتة',-1);
INSERT INTO `chartmaster` VALUES ('1825','مخصص اهلاك اثاث مكتبى و معدات','الاصول الثابتة',-1);
INSERT INTO `chartmaster` VALUES ('1840','سيارات','الاصول الثابتة',-1);
INSERT INTO `chartmaster` VALUES ('1845','مخصص اهلاك سيارات','الاصول الثابتة',-1);
INSERT INTO `chartmaster` VALUES ('2100','موردين','التزامات قصيرة الاجل',-1);
INSERT INTO `chartmaster` VALUES ('2160','ضريبة شركات مستحقة','التزامات قصيرة الاجل',-1);
INSERT INTO `chartmaster` VALUES ('2190','ضريبة دخل مستحقة','التزامات قصيرة الاجل',-1);
INSERT INTO `chartmaster` VALUES ('2210','عمال شركات الدائنة','التزامات قصيرة الاجل',-1);
INSERT INTO `chartmaster` VALUES ('2220','عطلة الأجر المستحق','التزامات قصيرة الاجل',-1);
INSERT INTO `chartmaster` VALUES ('2250','خطة المعاشات التقاعدية المستحقة','التزامات قصيرة الاجل',-1);
INSERT INTO `chartmaster` VALUES ('2260','تأمين فرص العمل الدائنة','التزامات قصيرة الاجل',-1);
INSERT INTO `chartmaster` VALUES ('2280','ضرائب مرتبات مستحقة','التزامات قصيرة الاجل',-1);
INSERT INTO `chartmaster` VALUES ('2310','ضريبة مبيعات (10%)','التزامات قصيرة الاجل',-1);
INSERT INTO `chartmaster` VALUES ('2320','ضريبة مبيعات (14%)','التزامات قصيرة الاجل',-1);
INSERT INTO `chartmaster` VALUES ('2330','ضريبة مبيعات (30%)','التزامات قصيرة الاجل',-1);
INSERT INTO `chartmaster` VALUES ('2620','قروض من البنوك','التمات طويلة الاجل',-1);
INSERT INTO `chartmaster` VALUES ('2680','قروض من حملة الاسهم','التمات طويلة الاجل',-1);
INSERT INTO `chartmaster` VALUES ('3350','الاسهم','حقوق الملكية',-1);
INSERT INTO `chartmaster` VALUES ('4020','مبيعات - قطع غيار','ايراد المبيعات',-1);
INSERT INTO `chartmaster` VALUES ('4030','مبيعات برامج','ايراد المبيعات',-1);
INSERT INTO `chartmaster` VALUES ('4040','مبيعات اخرى','ايراد المبيعات',-1);
INSERT INTO `chartmaster` VALUES ('4320','استشارات','راد استشارات',-1);
INSERT INTO `chartmaster` VALUES ('4330','برمجة','راد استشارات',-1);
INSERT INTO `chartmaster` VALUES ('4430','شحن و تعبئة','ايردات اخرى',-1);
INSERT INTO `chartmaster` VALUES ('4440','فائدة','ايردات اخرى',-1);
INSERT INTO `chartmaster` VALUES ('4450','ارباح تغيير عملة','ايردات اخرى',-1);
INSERT INTO `chartmaster` VALUES ('5010','مشتريات','تكلفة البضاعة المباعة',-1);
INSERT INTO `chartmaster` VALUES ('5020','تكلفة البضاعة المباعة - قطع غيار','تكلفة البضاعة المباعة',-1);
INSERT INTO `chartmaster` VALUES ('5030','تكلفة البضاعة المباعة - برامج','تكلفة البضاعة المباعة',-1);
INSERT INTO `chartmaster` VALUES ('5040','تكلفة البضاعة المباعة - اخرى','تكلفة البضاعة المباعة',-1);
INSERT INTO `chartmaster` VALUES ('5100','شحن','تكلفة البضاعة المباعة',-1);
INSERT INTO `chartmaster` VALUES ('5410','المرتبات','مصروفات الاجور',-1);
INSERT INTO `chartmaster` VALUES ('5420','نفقات التأمين على البطالة','مصروفات الاجور',-1);
INSERT INTO `chartmaster` VALUES ('5430','نفقات خطة المعاشات التقاعدية','مصروفات الاجور',-1);
INSERT INTO `chartmaster` VALUES ('5440','نفقة عمال شركات','مصروفات الاجور',-1);
INSERT INTO `chartmaster` VALUES ('5470','استحقاقات الموظفين','مصروفات الاجور',-1);
INSERT INTO `chartmaster` VALUES ('5610','قانونية و محاسبية','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5615','دعاية و اعلان','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5620','ديون معدومة','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5650','تكلفة رأس المال بدل نفقات','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5660','مصاريف اهلاك','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5680','ضريبة دخل','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5685','تامين','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5690','قوائد و مصاريف بنكية','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5700','مهمات مكتبية','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5760','ايجار','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5765','اصلاح و صيانة','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5780','تلفون','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5785','مصاريف سفر','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5790','مرافق','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5795','رسوم','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5800','رخص','مصروفات ادارية و عمومية',-1);
INSERT INTO `chartmaster` VALUES ('5810','خسارة تحويل عملة','مصروفات ادارية و عمومية',-1);
