package main

import (
	"fmt"
	"os"
	"strconv"
)

var do bool = true

func main() {
	filenames := []string{"testInput2", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		var total int
		for index := 0; index+len(PREFIX)+4 < len(fileContents); index++ {
			int1, int2 := tryReadStartingFromByte(fileContents, index, PREFIX)
			if int1 == nil {
				continue
			}
			if do {
				total += *int1 * *int2
			}
		}
		println(total)
	}
}

const PREFIX = "mul("
const COMMA = ","
const SUFFIX = ")"
const DO = "do()"
const DONT = "don't()"

func tryReadStartingFromByte(fileContents []byte, readStartIndex int, PREFIX string) (*int, *int) {

	doPrefix := string(fileContents[readStartIndex : readStartIndex+len(DO)])
	if doPrefix == DO {
		do = true
		return nil, nil
	}
	dontPrefix := string(fileContents[readStartIndex : readStartIndex+len(DONT)])
	if dontPrefix == DONT {
		do = false
		return nil, nil
	}
	prefix := string(fileContents[readStartIndex : readStartIndex+len(PREFIX)])
	if prefix != PREFIX {
		return nil, nil
	}

	var firstNumberString string
	var firstNumberStartingIndex int
	for firstNumberStartingIndex = readStartIndex + len(PREFIX); ; firstNumberStartingIndex++ {
		if firstNumberStartingIndex+3 >= len(fileContents) {
			//we need at least 3 characters ",1)" to finish reading, so if it's EOF, there is no valid string beyond
			return nil, nil
		}
		currentByteString := string(fileContents[firstNumberStartingIndex])
		if currentByteString == COMMA {
			break
		}
		_, err := strconv.Atoi(currentByteString)
		if err == nil {
			firstNumberString += currentByteString
		} else {
			//found something that doesn't convert to int before encountering a comma
			return nil, nil
		}
	}
	if len(firstNumberString) == 0 {
		return nil, nil
	}

	//firstNumberStartingIndex is at COMMA position now
	var secondNumberString string
	for secondNumberStartingIndex := firstNumberStartingIndex + len(COMMA); ; secondNumberStartingIndex++ {
		if secondNumberStartingIndex+1 >= len(fileContents) {
			//we need at least ")" to finish reading, so if it's EOF, there is no valid string beyond
			return nil, nil
		}
		currentByteString := string(fileContents[secondNumberStartingIndex])
		if currentByteString == SUFFIX {
			break
		}
		_, err := strconv.Atoi(currentByteString)
		if err == nil {
			secondNumberString += currentByteString
		} else {
			//found something that doesn't convert to int before encountering a comma
			return nil, nil
		}
	}

	if len(secondNumberString) == 0 {
		return nil, nil
	}

	firstNumber, err := strconv.Atoi(firstNumberString)
	if err != nil {
		panic(err)
	}
	secondNumber, err := strconv.Atoi(secondNumberString)
	if err != nil {
		panic(err)
	}

	return &firstNumber, &secondNumber
}
