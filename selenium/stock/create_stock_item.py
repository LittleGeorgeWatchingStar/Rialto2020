from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
from selenium.common.exceptions import NoSuchElementException
from random import randint
import unittest, time, re
import config

class CreateStockItem(unittest.TestCase):
    def setUp(self):
        self.driver = webdriver.Firefox()
        self.driver.implicitly_wait(30)
        self.base_url = config.rialto_url
        self.verificationErrors = []
        self.accept_next_alert = True
        self.user_email = config.test_account['email']
        self.user_password = config.test_account['password']
        self.accounts_url = config.accounts_url
        self.stock_code = "ROBOTEST0011"
        # a potential problem with using this code is
        # that since the test is to be ran many times
        # it will eventually use the same stock code to create
        # a 'new' item which will fail because there may already exist an item with such code 
    
    def test_create_stock_item(self):
        driver = self.driver
        self.fill_form()
        self.assertEqual("Created item " + self.stock_code + " successfully.", driver.find_element_by_css_selector("div.flash.flash-notice").text)
        self.assertEqual(self.stock_code, driver.find_element_by_id("StockItem_stockCode").text)
        
    def test_duplicate(self):
        self.fill_form()
        error_message = "The stock code " + self.stock_code + " is already in use."
        #self.assertEqual(u + error_message, self.driver.find_element_by_css_selector("li").text)
        self.assertEqual("Create a new stock item", self.driver.find_element_by_css_selector("#bodyContent > h1").text)

    def fill_form(self):
        driver = self.driver
        self.login_std()
        time.sleep(2)
        driver.get(self.base_url + "/index.php/")
        driver.find_element_by_link_text("Inventory").click()
        driver.find_element_by_link_text("Add Inventory Item").click()
        driver.find_element_by_id("StockItem_stockCode").clear()
        driver.find_element_by_id("StockItem_stockCode").send_keys(self.stock_code)
        driver.find_element_by_id("StockItem_category").send_keys("Board(Finished)")
        driver.find_element_by_id("StockItem_mbFlag").send_keys("manufactured")
        driver.find_element_by_id("StockItem_description").clear()
        driver.find_element_by_id("StockItem_description").send_keys("A robo board")
        driver.find_element_by_id("StockItem_longDescription").clear()
        driver.find_element_by_id("StockItem_longDescription").send_keys("for testing purposes...")
        driver.find_element_by_id("StockItem_orderQuantity").clear()
        driver.find_element_by_id("StockItem_orderQuantity").send_keys("10")
        driver.find_element_by_id("StockItem_temperature").clear()
        driver.find_element_by_id("StockItem_temperature").send_keys("10 - 20 Celsius")
        driver.find_element_by_id("StockItem_countryOfOrigin").send_keys("Canada")
        driver.find_element_by_id("StockItem_initialVersion_versionCode").clear()
        driver.find_element_by_id("StockItem_initialVersion_versionCode").send_keys("VER123")
        driver.find_element_by_id("StockItem_initialVersion_weight").clear()
        driver.find_element_by_id("StockItem_initialVersion_weight").send_keys("1")
        driver.find_element_by_id("StockItem_initialVersion_dimensions_x").clear()
        driver.find_element_by_id("StockItem_initialVersion_dimensions_x").send_keys("1")
        driver.find_element_by_id("StockItem_initialVersion_dimensions_y").clear()
        driver.find_element_by_id("StockItem_initialVersion_dimensions_y").send_keys("1")
        driver.find_element_by_id("StockItem_initialVersion_dimensions_z").clear()
        driver.find_element_by_id("StockItem_initialVersion_dimensions_z").send_keys("1")
        driver.find_element_by_id("StockItem_eccnCode").clear()
        driver.find_element_by_id("StockItem_eccnCode").send_keys("ECCNCODE")
        driver.find_element_by_id("StockItem_harmonizationCode").clear()
        driver.find_element_by_id("StockItem_harmonizationCode").send_keys("3902900010")
        driver.find_element_by_css_selector("button[type=\"submit\"]").click()
        
    
    def login_std(self):
        driver = self.driver
        driver.get(self.accounts_url)
        time.sleep(5)
        driver.find_element_by_link_text("Log in").click()
        driver.get(self.accounts_url + "/login/?next=/home/")
        driver.find_element_by_name("email").clear()
        driver.find_element_by_name("email").send_keys("test@gumstix.com")
        driver.find_element_by_name("password").clear()
        driver.find_element_by_name("password").send_keys("testing123")
        driver.find_element_by_css_selector("button.pure-button.pure-button-green").click()

    
    def is_element_present(self, how, what):
        try: self.driver.find_element(by=how, value=what)
        except NoSuchElementException, e: return False
        return True
    
    def is_alert_present(self):
        try: self.driver.switch_to_alert()
        except NoAlertPresentException, e: return False
        return True
    
    def close_alert_and_get_its_text(self):
        try:
            alert = self.driver.switch_to_alert()
            alert_text = alert.text
            if self.accept_next_alert:
                alert.accept()
            else:
                alert.dismiss()
            return alert_text
        finally: self.accept_next_alert = True
    
    def tearDown(self):
        self.driver.quit()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":
    unittest.main()
