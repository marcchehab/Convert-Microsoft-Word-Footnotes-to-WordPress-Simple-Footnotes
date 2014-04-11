Sub fieldcodetotext()
Dim MyString As String
Dim aFN As Footnote
ActiveWindow.View.ShowFieldCodes = True
For Each aFN In ActiveDocument.Footnotes
For Each aField In aFN.Range.Fields
aField.Select
MyString = "{ " & Selection.Fields(1).Code.Text & " }"
Selection.Text = MyString
Next aField
Next aFN
ActiveWindow.View.ShowFieldCodes = False
End Sub
